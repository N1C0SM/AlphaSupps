<?php
header('Content-Type: application/json');
session_start();

require '../config.php';
require '../models/order.php';
require_once '../models/db.php';
require_once '../models/pack.php';
require_once __DIR__ . '/../includes/pricing.php';

$conn = connectToDatabase();
$stripeSecretKey = STRIPE_SECRET_KEY;

$isLoggedIn = isset($_SESSION["user_id"]) || isset($_SESSION["user"]["id"]);

// -------------------------------
// Modularización interna
// -------------------------------

function leerEntradaJSON() {
    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);
    if (!$input) parse_str($raw, $input);
    return $input;
}

function validarAccion($input) {
    if (!$input || !isset($input["action"])) {
        respuestaError("No se recibió ninguna acción.");
    }
}

function respuestaError($msg) {
    echo json_encode(["success" => false, "error" => $msg]);
    exit;
}

function respuestaOK($data) {
    echo json_encode(array_merge(["success" => true], $data));
    exit;
}

function calcularDescuentoPack(int $count): float {
    if ($count >= 4) return 0.10;
    if ($count === 3) return 0.07;
    if ($count === 2) return 0.05;
    return 0.0;
}

function parseCartItems($input): array {
    $items = $input["items"] ?? [];
    if (!is_array($items)) {
        return [];
    }

    $clean = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $id = trim((string)($item["id"] ?? ""));
        $qty = max(1, intval($item["qty"] ?? 0));
        if ($id === "" || $qty <= 0) {
            continue;
        }

        $clean[] = [
            "id" => $id,
            "qty" => $qty,
            "subscribe" => !empty($item["subscribe"]),
            "interval" => intval($item["interval"] ?? 0),
            "type" => $item["type"] ?? null,
            "name" => $item["name"] ?? null,
            "items" => $item["items"] ?? null
        ];
    }

    return $clean;
}

function fetchSupplementRow(mysqli $conn, int $id): ?array {
    $stmt = $conn->prepare("
        SELECT id, name, price, margin_product, margin_subscription
        FROM supplements
        WHERE id = ?
        LIMIT 1
    ");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function fetchSupplementNames(mysqli $conn, array $ids): array {
    $ids = array_values(array_unique(array_map("intval", $ids)));
    if (!$ids) {
        return [];
    }

    $idsSql = implode(",", $ids);
    $result = $conn->query("SELECT id, name FROM supplements WHERE id IN ($idsSql)");
    if (!$result) {
        return [];
    }

    $names = [];
    while ($row = $result->fetch_assoc()) {
        $names[(int)$row["id"]] = (string)$row["name"];
    }

    return $names;
}

function formatItemsText(array $items): string {
    $parts = [];
    foreach ($items as $item) {
        $name = trim((string)($item["name"] ?? ""));
        if ($name === "") {
            continue;
        }
        $qty = max(1, intval($item["qty"] ?? 1));
        $parts[] = $qty . " x " . $name;
    }
    return implode(" | ", $parts);
}

function buildPackContents(mysqli $conn, array $pack): string {
    $features = $pack["features"] ?? [];
    if (!is_array($features) || !$features) {
        return "";
    }

    $items = [];
    $ids = [];
    $qtyById = [];

    foreach ($features as $feature) {
        if (is_array($feature)) {
            $name = trim((string)($feature["name"] ?? ""));
            $qty = max(1, intval($feature["qty"] ?? 1));
            if ($name !== "") {
                $items[] = ["name" => $name, "qty" => $qty];
                continue;
            }

            $id = intval($feature["id"] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
                $qtyById[$id] = max($qtyById[$id] ?? 0, $qty);
                continue;
            }

            $flat = [];
            foreach ($feature as $value) {
                if (is_string($value)) {
                    $text = trim($value);
                    if ($text !== "") {
                        $flat[] = $text;
                    }
                }
            }

            if ($flat) {
                $items[] = ["name" => implode(" ", $flat), "qty" => 1];
            }
        } else {
            $name = trim((string)$feature);
            if ($name !== "") {
                $items[] = ["name" => $name, "qty" => 1];
            }
        }
    }

    if ($ids) {
        $names = fetchSupplementNames($conn, $ids);
        foreach ($qtyById as $id => $qty) {
            if (!isset($names[$id])) {
                continue;
            }
            $items[] = ["name" => $names[$id], "qty" => $qty];
        }
    }

    return formatItemsText($items);
}

function resolveCustomPackDetails(mysqli $conn, array $subItems): ?array {
    $computed = computeCustomPackPrice($conn, $subItems);
    if (!empty($computed['error'])) {
        return null;
    }

    $items = [];
    foreach ($computed['features'] as $feature) {
        $items[] = [
            "name" => $feature['name'] ?? 'Producto',
            "qty" => $feature['qty'] ?? 1
        ];
    }

    return [
        "total" => $computed['total'] ?? 0,
        "items" => formatItemsText($items),
        "resolved" => $computed['features'] ?? []
    ];
}

/**
 * Precio final por ítem aplicando márgenes y suscripción.
 */
function resolveItemPricing(mysqli $conn, array $item): array {
    $qty = max(1, intval($item["qty"] ?? 1));
    $rawId = (string)($item["id"] ?? "");
    $subscribe = !empty($item["subscribe"]);
    $interval = intval($item["interval"] ?? 0);
    $name = null;
    $unitPrice = null;
    $description = '';

    if (preg_match('/^(pack|supplement)_(\\d+)(?:_sub_(\\d+))?$/', $rawId, $matches)) {
        $type = $matches[1];
        $id = intval($matches[2]);
        $interval = $interval ?: intval($matches[3] ?? 0);
        if (!empty($matches[3])) {
            $subscribe = true;
        }

        if ($type === "pack") {
            $pack = getPackById($conn, $id);
            if (!$pack) {
                return ["error" => "Pack no encontrado"];
            }
            $name = $pack["name"] ?? "Pack";
            $contents = buildPackContents($conn, $pack);
            if ($contents !== "") {
                $name .= " [incluye: " . $contents . "]";
                $description = $contents;
            }
            $basePrice = null;
            if (!empty($pack['price_numeric'])) {
                $basePrice = floatval($pack['price_numeric']);
            } elseif (isset($pack['price'])) {
                $basePrice = floatval($pack['price']);
            }
            if ($basePrice === null || $basePrice <= 0) {
                return ["error" => "Precio de pack inválido"];
            }
            $packMargin = pricingMargins()['pack'];
            if (!empty($pack['margin_pack'])) {
                $packMargin = floatval($pack['margin_pack']);
            }
            $unitPrice = $basePrice + ($basePrice * $packMargin);
        } else {
            $supplement = fetchSupplementRow($conn, $id);
            if (!$supplement) {
                return ["error" => "Suplemento no encontrado"];
            }
            $name = $supplement["name"] ?? "Suplemento";
            $pricing = computeSupplementPrice($supplement, $subscribe, false);
            if (!empty($pricing['error'])) {
                return ["error" => $pricing['error']];
            }
            $unitPrice = $pricing['price'];
        }
    } elseif (($item["type"] ?? "") === "custom_pack" || strpos($rawId, "custom_pack_") === 0) {
        $subItems = is_array($item["items"] ?? null) ? $item["items"] : [];
        $customDetails = resolveCustomPackDetails($conn, $subItems);
        if ($customDetails === null) {
            return ["error" => "Pack personalizado inválido"];
        }
        $name = $item["name"] ?? "Pack personalizado";
        if (!empty($customDetails["items"])) {
            $name .= " [incluye: " . $customDetails["items"] . "]";
            $description = $customDetails["items"];
        }
        $unitPrice = floatval($customDetails["total"] ?? 0);
    } else {
        return ["error" => "Producto inválido en el carrito"];
    }

    if ($unitPrice === null || $unitPrice <= 0) {
        return ["error" => "Precio inválido en el carrito"];
    }

    if ($subscribe) {
        $unitPrice = $unitPrice * 0.9;
    }

    return [
        "name" => $name,
        "unit_price" => round($unitPrice, 2),
        "qty" => $qty,
        "subscribe" => $subscribe,
        "interval" => $interval,
        "description" => $description
    ];
}

function resolveCartTotals(mysqli $conn, array $items): array {
    $total = 0.0;
    $quantity = 0;
    $productParts = [];

    foreach ($items as $item) {
        $pricing = resolveItemPricing($conn, $item);
        if (!empty($pricing["error"])) {
            return ["error" => $pricing["error"]];
        }

        $qty = $pricing["qty"];
        $unitPrice = $pricing["unit_price"];
        $name = $pricing["name"];
        $subscribe = $pricing["subscribe"];
        $interval = $pricing["interval"];

        $lineTotal = $unitPrice * $qty;
        $total += $lineTotal;
        $quantity += $qty;

        $subscriptionLabel = $subscribe && $interval > 0
            ? " (Suscripción cada {$interval} meses)"
            : "";
        $productParts[] = "{$name}{$subscriptionLabel} x{$qty}";
    }

    if ($quantity <= 0 || $total <= 0) {
        return ["error" => "Carrito vacio"];
    }

    return [
        "total" => round($total, 2),
        "quantity" => $quantity,
        "product" => implode(", ", $productParts)
    ];
}

function buildStripeLineItems(mysqli $conn, array $items): array {
    $subscriptionItems = [];
    $oneTimeItems = [];

    foreach ($items as $item) {
        $pricing = resolveItemPricing($conn, $item);
        if (!empty($pricing["error"])) {
            return ["error" => $pricing["error"]];
        }

        $qty = $pricing["qty"];
        $unitPrice = $pricing["unit_price"];
        $name = $pricing["name"];
        $subscribe = $pricing["subscribe"];
        $interval = $pricing["interval"] ?: 1;

        if ($subscribe) {
            $subscriptionItems[] = [
                "name" => $name,
                "amount" => $unitPrice,
                "interval" => $interval,
                "qty" => $qty
            ];
        } else {
            $oneTimeItems[] = [
                "name" => $name,
                "amount" => $unitPrice,
                "qty" => $qty
            ];
        }
    }

    return [
        "subscriptions" => $subscriptionItems,
        "one_time" => $oneTimeItems
    ];
}

// -------------------------------
// Alpha: regalo backend-only
// -------------------------------

function leerTimestampEnv(string $key): ?int {
    $value = trim((string) getenv($key));
    if ($value === '') return null;

    $ts = strtotime($value);
    if ($ts !== false) return $ts;

    // Normalizar formatos comunes abreviados:
    // - "YYYY-MM-DD HH" -> "YYYY-MM-DD HH:00:00"
    // - "YYYY-MM-DD HH:MM" -> "YYYY-MM-DD HH:MM:00"
    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}\\s+\\d{1,2}$/', $value)) {
        $ts = strtotime($value . ':00:00');
        if ($ts !== false) return $ts;
    }

    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}\\s+\\d{1,2}:\\d{2}$/', $value)) {
        $ts = strtotime($value . ':00');
        if ($ts !== false) return $ts;
    }

    return null;
}

function estaDentroPeriodoAlpha(): bool {
    $start = leerTimestampEnv('ALPHA_GIFT_START');
    $end   = leerTimestampEnv('ALPHA_GIFT_END');

    // Si no está configurado, por defecto NO hay periodo Alpha activo.
    if ($start === null || $end === null) return false;

    $now = time();
    return $now >= $start && $now <= $end;
}

function emailEstaEnRegistrosPrelanzamiento(mysqli $conn, string $email): bool {
    $email = strtolower(trim($email));
    if ($email === '') return false;

    $stmt = $conn->prepare("SELECT 1 FROM registros_prelanzamiento WHERE LOWER(email) = ? LIMIT 1");
    if (!$stmt) return false;

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

function obtenerRegaloAlpha(mysqli $conn, string $email): ?string {
    if (!estaDentroPeriodoAlpha()) return null;
    if (!emailEstaEnRegistrosPrelanzamiento($conn, $email)) return null;

    $gift = trim((string) getenv('ALPHA_GIFT_CODE'));
    if ($gift === '') $gift = 'prozis_bar';

    return $gift;
}

function accionCrearPago($input, $stripeSecretKey, $conn) {
    $items = parseCartItems($input);
    $precio = null;
    $hasSubscription = false;

    if ($items) {
        foreach ($items as $it) {
            if (!empty($it["subscribe"])) {
                $hasSubscription = true;
                break;
            }
        }

        $totals = resolveCartTotals($conn, $items);
        if (!empty($totals["error"])) {
            respuestaError($totals["error"]);
        }
        $precio = $totals["total"] ?? null;

        // Si hay suscripciones, crear Subscription en Stripe
        if ($hasSubscription) {
            accionCrearSuscripcion($input, $stripeSecretKey, $conn, $items);
            return;
        }
    }

    if ($precio === null) {
        if (!isset($input["precio"]) || floatval($input["precio"]) <= 0) {
            respuestaError("El precio es obligatorio.");
        }
        $precio = floatval($input["precio"]);
    }

    $resultado = crearPago($precio, $stripeSecretKey);

    if (!empty($resultado["error"])) {
        respuestaError($resultado["error"]);
    }

    respuestaOK(["client_secret" => $resultado["client_secret"]]);
}

function stripeRequest(string $url, array $payload, string $secretKey, string $method = 'POST'): array {
    $ch = curl_init();

    if (strtoupper($method) === 'GET') {
        $query = $payload ? ('?' . http_build_query($payload)) : '';
        curl_setopt($ch, CURLOPT_URL, $url . $query);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ":");

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ["error" => $error ?: "Error desconocido al conectar con Stripe"];
    }
    curl_close($ch);

    $decoded = json_decode($response, true);
    if ($httpCode >= 400 || isset($decoded["error"])) {
        $msg = $decoded["error"]["message"] ?? "Stripe devolvió un error ({$httpCode})";
        return ["error" => $msg, "stripe_response" => $decoded];
    }

    return $decoded ?: ["error" => "Respuesta vacía de Stripe"];
}

/**
 * Intenta obtener un client_secret de payment_intent o setup_intent asociado a la suscripción.
 * Si no existe, finaliza la factura y genera un PaymentIntent manual.
 */
function ensureSubscriptionClientSecret(string $subscriptionId, ?string $invoiceId, string $customerId, string $stripeSecretKey): array {
    // 0) Si no hay invoiceId, intentar recuperarlo desde la suscripción
    if (!$invoiceId) {
        $sub = stripeRequest("https://api.stripe.com/v1/subscriptions/{$subscriptionId}", [
            "expand[0]" => "latest_invoice.payment_intent"
        ], $stripeSecretKey, 'GET');
        if (empty($sub['error'])) {
            $inv = $sub['latest_invoice'] ?? null;
            if (is_array($inv)) {
                $invoiceId = $inv['id'] ?? $invoiceId;
                $pi = $inv['payment_intent'] ?? null;
                if (is_array($pi) && !empty($pi['client_secret'])) {
                    return [
                        'client_secret' => $pi['client_secret'],
                        'payment_intent_id' => $pi['id'] ?? null,
                        'invoice_id' => $invoiceId
                    ];
                }
            }
        }
    }

    // 1) Expandir factura para obtener payment_intent
    if ($invoiceId) {
        $invoice = stripeRequest("https://api.stripe.com/v1/invoices/{$invoiceId}", [
            "expand[0]" => "payment_intent",
            "expand[1]" => "subscription"
        ], $stripeSecretKey, 'GET');
        if (empty($invoice['error'])) {
            $pi = $invoice['payment_intent'] ?? null;
            if (is_array($pi) && !empty($pi['client_secret'])) {
                return [
                    'client_secret' => $pi['client_secret'],
                    'payment_intent_id' => $pi['id'] ?? null,
                    'invoice_id' => $invoiceId
                ];
            }
        }
    }

    // 2) Finalizar la factura para forzar creación de PI
    if ($invoiceId) {
        $finalize = stripeRequest("https://api.stripe.com/v1/invoices/{$invoiceId}/finalize", [
            "expand[0]" => "payment_intent"
        ], $stripeSecretKey, 'POST');
        if (empty($finalize['error'])) {
            $pi = $finalize['payment_intent'] ?? null;
            if (is_array($pi) && !empty($pi['client_secret'])) {
                return [
                    'client_secret' => $pi['client_secret'],
                    'payment_intent_id' => $pi['id'] ?? null,
                    'invoice_id' => $invoiceId
                ];
            }
        }
    }

    // 3) Crear PaymentIntent manual con total de la factura si lo tenemos
    if ($invoiceId) {
        $invoice = stripeRequest("https://api.stripe.com/v1/invoices/{$invoiceId}", [], $stripeSecretKey, 'GET');
        if (empty($invoice['error']) && !empty($invoice['total']) && !empty($invoice['currency'])) {
            $manualPI = stripeRequest("https://api.stripe.com/v1/payment_intents", [
                "amount" => intval($invoice["total"]),
                "currency" => $invoice["currency"],
                "customer" => $customerId,
                "automatic_payment_methods[enabled]" => "true",
                "metadata[subscription_id]" => $subscriptionId,
                "metadata[invoice_id]" => $invoiceId,
                "metadata[origin]" => "alphasupps"
            ], $stripeSecretKey, 'POST');
            if (empty($manualPI["error"]) && !empty($manualPI["client_secret"])) {
                return [
                    'client_secret' => $manualPI['client_secret'],
                    'payment_intent_id' => $manualPI['id'] ?? null,
                    'invoice_id' => $invoiceId
                ];
            }
        }
    }

    return ['error' => 'No se pudo obtener client_secret para la suscripción'];
}

function accionCrearSuscripcion($input, $stripeSecretKey, $conn, array $items) {
    $lines = buildStripeLineItems($conn, $items);
    if (!empty($lines["error"])) {
        respuestaError($lines["error"]);
    }
    if (empty($lines["subscriptions"])) {
        respuestaError("No hay productos de suscripción en el carrito.");
    }

    $customerData = $input["customer"] ?? [];
    $email = trim((string)($customerData["email"] ?? ""));
    $name = trim((string)($customerData["name"] ?? "Cliente AlphaSupps"));
    $address = trim((string)($customerData["address"] ?? ""));
    $postal = trim((string)($customerData["postal_code"] ?? ""));
    $phone = trim((string)($customerData["phone"] ?? ""));

    if ($email === "") {
        respuestaError("El email es obligatorio para crear la suscripción.");
    }

    // 1) Crear cliente en Stripe
    $customerPayload = [
        "email" => $email,
        "name" => $name,
        "phone" => $phone,
        "address[line1]" => $address,
        "address[postal_code]" => $postal,
        "metadata[origin]" => "alphasupps",
        "metadata[source]" => "checkout"
    ];
    $customerRes = stripeRequest("https://api.stripe.com/v1/customers", $customerPayload, $stripeSecretKey);
    if (!empty($customerRes["error"])) {
        respuestaError($customerRes["error"]);
    }
    $customerId = $customerRes["id"] ?? null;
    if (!$customerId) {
        respuestaError("No se pudo crear el cliente en Stripe.");
    }

    // Helper para crear un Product en Stripe
    $createProduct = function(string $productName) use ($stripeSecretKey) {
        $res = stripeRequest("https://api.stripe.com/v1/products", [
            "name" => $productName,
            "metadata[origin]" => "alphasupps"
        ], $stripeSecretKey);
        if (!empty($res["error"])) {
            respuestaError("No se pudo crear producto en Stripe: " . $res["error"]);
        }
        return $res["id"] ?? null;
    };

    // 2) Crear suscripción con invoice + PaymentIntent
    $payload = [
        "customer" => $customerId,
        "payment_behavior" => "default_incomplete",
        "collection_method" => "charge_automatically",
        "payment_settings[save_default_payment_method]" => "on_subscription",
        "payment_settings[payment_method_types][0]" => "card",
        "expand[0]" => "latest_invoice.payment_intent",
        "expand[1]" => "pending_setup_intent",
        "metadata[origin]" => "alphasupps",
        "metadata[source]" => "checkout"
    ];

    foreach ($lines["subscriptions"] as $idx => $sub) {
        $amountCents = intval(round($sub["amount"] * 100));
        if ($amountCents <= 0) {
            respuestaError("Importe inválido para suscripción.");
        }
        $intervalCount = max(1, intval($sub["interval"]));
        $productId = $createProduct($sub["name"]);
        if (!$productId) {
            respuestaError("No se pudo crear el producto de suscripción en Stripe.");
        }
        $payload["items[{$idx}][price_data][currency]"] = "eur";
        $payload["items[{$idx}][price_data][product]"] = $productId;
        $payload["items[{$idx}][price_data][recurring][interval]"] = "month";
        $payload["items[{$idx}][price_data][recurring][interval_count]"] = $intervalCount;
        $payload["items[{$idx}][price_data][unit_amount]"] = $amountCents;
        $payload["items[{$idx}][quantity]"] = max(1, intval($sub["qty"]));
    }

    foreach ($lines["one_time"] as $idx => $item) {
        $amountCents = intval(round($item["amount"] * 100));
        if ($amountCents <= 0) {
            respuestaError("Importe inválido en producto único.");
        }
        $productId = $createProduct($item["name"]);
        if (!$productId) {
            respuestaError("No se pudo crear producto en Stripe para el cargo único.");
        }
        $payload["add_invoice_items[{$idx}][price_data][currency]"] = "eur";
        $payload["add_invoice_items[{$idx}][price_data][product]"] = $productId;
        $payload["add_invoice_items[{$idx}][price_data][unit_amount]"] = $amountCents;
        $payload["add_invoice_items[{$idx}][quantity]"] = max(1, intval($item["qty"]));
    }

    $subRes = stripeRequest("https://api.stripe.com/v1/subscriptions", $payload, $stripeSecretKey);
    if (!empty($subRes["error"])) {
        respuestaError($subRes["error"]);
    }

    $paymentIntent = $subRes["latest_invoice"]["payment_intent"] ?? null;
    $clientSecret = $paymentIntent["client_secret"] ?? null;
    $invoiceId = is_array($subRes["latest_invoice"] ?? null)
        ? ($subRes["latest_invoice"]["id"] ?? null)
        : ($subRes["latest_invoice"] ?? null);
    if (!$clientSecret && isset($subRes["pending_setup_intent"]["client_secret"])) {
        // Suscripciones gratuitas o sin PI generan SetupIntent
        $clientSecret = $subRes["pending_setup_intent"]["client_secret"];
        $paymentIntent = $subRes["pending_setup_intent"];
    }
    if (!$clientSecret && $invoiceId) {
        $invoiceRes = stripeRequest("https://api.stripe.com/v1/invoices/{$invoiceId}", [
            "expand[0]" => "payment_intent"
        ], $stripeSecretKey, 'GET');
        if (!empty($invoiceRes["error"])) {
            $log = __DIR__ . '/../cache/stripe_subscription_response.log';
            file_put_contents($log, json_encode([
                "invoice_lookup_error" => $invoiceRes,
                "invoice_id" => $invoiceId,
                "timestamp" => date('c')
            ]) . PHP_EOL, FILE_APPEND);
        } else {
            $paymentIntent = $invoiceRes["payment_intent"] ?? null;
            if (is_array($paymentIntent) && !empty($paymentIntent["client_secret"])) {
                $clientSecret = $paymentIntent["client_secret"];
            } else {
                // Intentar finalizar la factura para generar payment_intent
                $finalize = stripeRequest("https://api.stripe.com/v1/invoices/{$invoiceId}/finalize", [
                    "expand[0]" => "payment_intent"
                ], $stripeSecretKey, 'POST');
                if (empty($finalize["error"])) {
                    $paymentIntent = $finalize["payment_intent"] ?? null;
                    if (is_array($paymentIntent) && !empty($paymentIntent["client_secret"])) {
                        $clientSecret = $paymentIntent["client_secret"];
                    }
                }
            }
        }
    }
    $paymentIntentId = $paymentIntent["id"] ?? null;
    $subscriptionId = $subRes["id"] ?? null;

    // Fallback robusto: asegurar client_secret (finalizar factura o PI manual)
    if (!$clientSecret) {
        $ensured = ensureSubscriptionClientSecret($subscriptionId, $invoiceId, $customerId, $stripeSecretKey);
        if (empty($ensured['error'])) {
            $clientSecret = $ensured['client_secret'] ?? $clientSecret;
            $paymentIntentId = $ensured['payment_intent_id'] ?? $paymentIntentId;
            $invoiceId = $ensured['invoice_id'] ?? $invoiceId;
        }
    }

    // Log de respuesta para depurar
    $debugLog = __DIR__ . '/../cache/stripe_subscription_response.log';
    $logPayload = [
        "request" => $payload,
        "response" => $subRes,
        "timestamp" => date('c')
    ];
    file_put_contents($debugLog, json_encode($logPayload) . PHP_EOL, FILE_APPEND);

    if (!$clientSecret || !$subscriptionId) {
        respuestaError("No pudimos iniciar el pago de suscripción.");
    }

    respuestaOK([
        "client_secret" => $clientSecret,
        "subscription_id" => $subscriptionId,
        "payment_intent_id" => $paymentIntentId,
        "customer_id" => $customerId,
        "invoice_id" => $invoiceId,
        "mode" => "subscription"
    ]);
}

function accionGuardarPedido($input, $isLoggedIn, $conn) {
    $input["user_id"] = $isLoggedIn ? ($_SESSION["user_id"] ?? $_SESSION["user"]["id"]) : null;
    $stripeSecretKey = STRIPE_SECRET_KEY;

    $items = parseCartItems($input);
    if ($items) {
        $totals = resolveCartTotals($conn, $items);
        if (!empty($totals["error"])) {
            respuestaError($totals["error"]);
        }
        $input["product"] = $totals["product"] ?? $input["product"] ?? "";
        $input["quantity"] = $totals["quantity"] ?? $input["quantity"] ?? 0;
        $input["price"] = $totals["total"] ?? $input["price"] ?? 0;
        $input["items"] = $items;
    }
    if (empty($input["source"])) {
        $input["source"] = "web";
    }

    if (
        empty($input["product"]) ||
        empty($input["quantity"]) ||
        empty($input["price"]) ||
        floatval($input["price"]) <= 0 ||
        empty($input["payment_id"])
    ) {
        respuestaError("Datos del pedido inválidos.");
    }

    try {
        // Ya hemos confirmado el PaymentIntent en el cliente; no forzar pay de la invoice para suscripción

        // El regalo SOLO se decide en backend (no confiar en input del cliente).
        $input["gift"] = obtenerRegaloAlpha($conn, (string)($input["email"] ?? ""));

        $order = guardarPedido($conn, $input);

        if (!$order || empty($order["id"])) {
            respuestaError("No se pudo guardar el pedido.");
        }

        respuestaOK([
            "order" => $order,
            "order_id" => $order["id"],
            "gift" => $order["gift"] ?? null
        ]);

    } catch (Exception $e) {
        respuestaError($e->getMessage());
    }
}

// -------------------------------
// Flujo principal
// -------------------------------

$input = leerEntradaJSON();
validarAccion($input);

switch ($input["action"]) {

    case "crear_pago":
        accionCrearPago($input, $stripeSecretKey, $conn);
        break;

    case "guardar_pedido":
        accionGuardarPedido($input, $isLoggedIn, $conn);
        break;

    default:
        respuestaError("Acción no reconocida.");
}
