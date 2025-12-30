<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/../phpMailer/PHPMailer.php';
require_once __DIR__ . '/../phpMailer/Exception.php';
require_once __DIR__ . '/../phpMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/**
 * 📌 Obtiene todos los pedidos (ordenados del más reciente al más antiguo)
 */
function getAllOrders($conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $sql = "SELECT * FROM orders ORDER BY id DESC";
    $result = $conn->query($sql);

    if (!$result) return [];

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * 📌 Obtiene un pedido por ID
 */
function getOrderById($id, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc() ?: null;
}

/**
 * 📌 Alterna el estado de envío/pago (has_paid)
 *   Si está en 1 → pasa a 0
 *   Si está en 0 → pasa a 1
 */
function toggleSentStatus($id, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("SELECT has_sent FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    if (!$result) return false;

    $current = (int)$result['has_sent'];
    $new = $current ? 0 : 1;

    $stmt = $conn->prepare("UPDATE orders SET has_sent = ? WHERE id = ?");
    $stmt->bind_param("ii", $new, $id);

    return $stmt->execute();
}
/****************************************************************************************
 * 1. VALIDAR JSON RECIBIDO
 ****************************************************************************************/
function procesarDatosPedido($rawInput): array {

    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error en el JSON: " . json_last_error_msg());
    }

    $required = [
        "name",
        "email",
        "address",
        "postal_code",
        "phone",
        "product",
        "quantity",
        "price",
        "payment_id"
    ];

    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === "") {
            throw new Exception("Falta el campo obligatorio '$field'");
        }
    }
    return $data;
}


/****************************************************************************************
 * 2. GUARDAR PEDIDO EN BASE DE DATOS
 ****************************************************************************************/
function guardarPedido($conn, array $data): array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId   = isset($data["user_id"]) && $data["user_id"] !== '' ? intval($data["user_id"]) : null;
    $name     = (string) $data["name"];
    $email    = (string) $data["email"];
    $address  = (string) $data["address"];
    $postal   = (string) $data["postal_code"];
    $phone    = (string) $data["phone"];
    $product  = (string) $data["product"];
    $quantity = (int)    $data["quantity"];
    $price    = (float)  $data["price"];
    $payment  = (string) $data["payment_id"];
    $gift     = isset($data["gift"]) && $data["gift"] !== "" ? (string) $data["gift"] : null;
    $subscriptionId = isset($data["subscription_id"]) && $data["subscription_id"] !== "" ? (string) $data["subscription_id"] : null;
    $itemsJson = null;
    if (!empty($data["items"]) && is_array($data["items"])) {
        $itemsJson = json_encode($data["items"]);
    } elseif (!empty($data["items_json"])) {
        $itemsJson = is_string($data["items_json"]) ? $data["items_json"] : json_encode($data["items_json"]);
    }
    $source = !empty($data["source"]) ? (string)$data["source"] : 'web';

    $stmt = $conn->prepare("
        INSERT INTO orders
        (user_id, name, email, address, postal_code, phone, product, quantity, price, payment_id, items_json, subscription_id, gift, fulfillment_status, tracking_code, tracking_url, source, has_sent, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NULL, NULL, ?, 0, NOW())
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar SQL: " . $conn->error);
    }

    $stmt->bind_param(
        "issssssidsssss",
        $userId,
        $name,
        $email,
        $address,
        $postal,
        $phone,
        $product,
        $quantity,
        $price,
        $payment,
        $itemsJson,
        $subscriptionId,
        $gift,
        $source
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar SQL: " . $stmt->error);
    }

    // ID del pedido recién creado
    $pedido_id = $conn->insert_id;
    $stmt->close();

    // Pedido completo
    $order = [
        "id"          => $pedido_id,
        "name"        => $name,
        "email"       => $email,
        "address"     => $address,
        "postal_code" => $postal,
        "phone"       => $phone,
        "product"     => $product,
        "quantity"    => $quantity,
        "price"       => $price,
        "payment_id"  => $payment,
        "gift"        => $gift,
        "subscription_id" => $subscriptionId,
        "fulfillment_status" => 'pending',
        "tracking_code" => null,
        "tracking_url" => null,
        "items"       => $data["items"] ?? null,
        "user_id"     => $userId,
        "source"      => $source
    ];

    // Guardar/actualizar registro de suscripción si aplica
    $createdUser = null;

    if ($subscriptionId) {
        // Asegurar un user_id válido para la FK: si no hay sesión, mapear/crear por email
        $subUserId = $userId;
        if ($subUserId === null) {
            if ($email !== '') {
                $existing = getUserByEmail($email, $conn);
                if ($existing && isset($existing['id'])) {
                    $subUserId = (int)$existing['id'];
                } else {
                    $randomPass = bin2hex(random_bytes(8));
                    $created = createUser($name ?: 'Invitado', $email, $randomPass, 'user', $conn);
                    if (!empty($created['success'])) {
                        $found = getUserByEmail($email, $conn);
                        if ($found && isset($found['id'])) {
                            $subUserId = (int)$found['id'];
                            $createdUser = $found;
                        }
                    }
                }
            }
            if ($subUserId === null) {
                throw new Exception("No se pudo asociar usuario para la suscripción (email requerido).");
            }
        }

        // Si se creó un usuario y no hay sesión activa, iniciar sesión para que pueda gestionar sus suscripciones
        if (!isset($_SESSION['user']) && $createdUser && isset($createdUser['id'])) {
            $_SESSION['user'] = [
                'id' => (int)$createdUser['id'],
                'name' => $createdUser['name'] ?? $name ?? 'Cliente',
                'email' => $createdUser['email'] ?? $email,
                'role' => $createdUser['role'] ?? 'user',
                'login_time' => date('Y-m-d H:i:s')
            ];
            $order['user_id'] = (int)$createdUser['id'];
        }
        $subData = json_encode([
            'order_id' => $pedido_id,
            'items' => $data["items"] ?? [],
            'email' => $email
        ]);
        $nextBilling = date('Y-m-d H:i:s', strtotime('+1 month'));
        $now = date('Y-m-d H:i:s');

        $existing = null;
        $check = $conn->prepare("SELECT id FROM subscriptions WHERE stripe_subscription_id = ? LIMIT 1");
        if ($check) {
            $check->bind_param("s", $subscriptionId);
            $check->execute();
            $res = $check->get_result();
            $existing = $res ? $res->fetch_assoc() : null;
            $check->close();
        }

        if ($existing) {
            $upd = $conn->prepare("
                UPDATE subscriptions
                SET user_id = ?, subscription_data = ?, status = 'active', total = ?, last_renewal = ?, next_billing = ?
                WHERE stripe_subscription_id = ?
            ");
            if ($upd) {
                $upd->bind_param("isdsss", $subUserId, $subData, $price, $now, $nextBilling, $subscriptionId);
                $upd->execute();
                $upd->close();
            }
        } else {
            $ins = $conn->prepare("
                INSERT INTO subscriptions (user_id, subscription_data, stripe_subscription_id, status, total, created_at, activated_at, last_renewal, next_billing)
                VALUES (?, ?, ?, 'active', ?, ?, ?, ?, ?)
            ");
            if ($ins) {
                $ins->bind_param("issdssss", $subUserId, $subData, $subscriptionId, $price, $now, $now, $now, $nextBilling);
                $ins->execute();
                $ins->close();
            }
        }
    }

    // Registrar ID de suscripción en log (sin romper esquema)
    if ($subscriptionId) {
        $logDir = __DIR__ . '/../cache';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/stripe_subscriptions.log';
        $logPayload = [
            "order_id" => $pedido_id,
            "subscription_id" => $subscriptionId,
            "payment_id" => $payment,
            "email" => $email,
            "created_at" => date('c')
        ];
        file_put_contents($logFile, json_encode($logPayload) . PHP_EOL, FILE_APPEND);
    }

    // -----------------------------------------------------
    // ✔️ CONSTRUIR URL DE LA FACTURA Y ENVIAR EMAIL
    // -----------------------------------------------------
    try {
        // TU URL DE FACTURA:
        $token = rawurlencode((string)$payment);
        $urlFactura = "https://alphasupps.alwaysdata.net/views/invoice.php?id=" . $pedido_id . "&token=" . $token;

        // Enviar mail
        enviarFacturaEmail($order, $urlFactura);

        // Marcar como enviado
        $conn->query("UPDATE orders SET has_sent = 1 WHERE id = {$pedido_id}");
    } catch (Exception $e) {
        error_log("Error enviando factura del pedido $pedido_id: " . $e->getMessage());
    }

    return $order;
}


/****************************************************************************************
 * 4. ENVIAR FACTURA POR EMAIL
 ****************************************************************************************/
function enviarFacturaEmail(array $order, string $urlFactura): bool {
    $subject = "Factura de tu pedido #{$order['id']}";

    $giftBlock = '';
    if (!empty($order['gift'])) {
        $giftSafe = htmlspecialchars((string)$order['gift'], ENT_QUOTES, 'UTF-8');
        $giftBlock = "
        <div style='background:rgba(245,210,122,0.12); border:1px solid rgba(245,210,122,0.35); padding:14px 16px; border-radius:10px; margin:18px 0;'>
            <p style='margin:0; font-size:15px; line-height:1.5;'>
                <strong>🎁 Regalo Alpha incluido:</strong> {$giftSafe}
            </p>
        </div>";
    }

    $html = "
<div style='background:#0d0d0f;padding:30px;color:#eee;font-family:Inter,Arial;border-radius:14px;max-width:580px;margin:auto;border:1px solid rgba(255,255,255,0.1);'>
    <div style='text-align:center;margin-bottom:20px;'>
        <svg class='logo-icon' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' style='width:60px;height:60px;color:#f5d27a;'>
          <g fill='none' stroke='currentColor' stroke-width='6' stroke-linecap='round' stroke-linejoin='round'>
            <circle cx='50' cy='50' r='46'/>
            <path d='M50 18 L72 75 L28 75 Z'/>
            <path d='M47 32 C49 38, 53 42, 51 47 C49 52, 43 56, 46 61 L56 72'/>
          </g>
        </svg>
    </div>
    <h2 style='color:#f5d27a;text-align:center;font-size:26px;margin-bottom:25px;font-weight:700;'>
        ¡Gracias por tu compra, {$order['name']}!
    </h2>

    <p style='font-size:16px;line-height:1.6;margin-bottom:20px;'>
        Hemos procesado tu pedido correctamente. Ya puedes descargar tu factura desde el siguiente enlace:
    </p>
    {$giftBlock}

    <div style='text-align:center;margin:35px 0;'>
        <a href='{$urlFactura}'
           style='padding:14px 22px;background:#f5d27a;color:#111;font-weight:bold;
                  text-decoration:none;border-radius:8px;font-size:17px;
                  display:inline-block;box-shadow:0 4px 12px rgba(0,0,0,0.3);'>
            Descargar Factura
        </a>
    </div>

    <p style='font-size:15px;line-height:1.5;color:#ccc;'>
        Si tienes alguna duda sobre tu pedido o necesitas soporte, puedes responder directamente a este email.
    </p>

    <p style='margin-top:30px;font-size:14px;color:#888;text-align:center;'>
        AlphaSupps © " . date('Y') . " — Todos los derechos reservados.
    </p>
</div>";
    return sendMail($order["email"], $subject, $html);
}


/****************************************************************************************
 * 5. SMTP — FUNCIÓN GENÉRICA PARA ENVIAR EMAILS
 ****************************************************************************************/
function sendMail(string $to, string $subject, string $htmlBody): bool {

    $mail = new PHPMailer(true);

    try {
        // Configuración de codificación para evitar problemas con tildes/ñ en asuntos
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->isSMTP();
        $mail->Host       = 'smtp-alphasupps.alwaysdata.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('alphasupps@alwaysdata.net', 'AlphaSupps');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        return $mail->send();

    } catch (Exception $e) {

        file_put_contents(__DIR__ . "/mail_error.log",
            "[" . date("Y-m-d H:i:s") . "] Error enviando email: " . $e->getMessage() . "\n",
            FILE_APPEND
        );

        return false;
    }
}


/****************************************************************************************
 * 6. CREAR PAGO STRIPE
 ****************************************************************************************/
function crearPago($precio, $stripeSecretKey) {

    $precio = floatval($precio) * 100;

    $ch = curl_init("https://api.stripe.com/v1/payment_intents");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $stripeSecretKey . ":");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "amount" => $precio,
        "currency" => "eur",
        "automatic_payment_methods[enabled]" => "true"
    ]));

    $response = curl_exec($ch);

    if ($response === false) {
        return ["error" => "Error en la conexión con Stripe."];
    }

    $decoded = json_decode($response, true);

    if (isset($decoded["client_secret"])) {
        return ["client_secret" => $decoded["client_secret"]];
    }

    return [
        "error" => "Stripe no devolvió client_secret.",
        "stripe_response" => $decoded
    ];
}
function getOrdersByEmail($email) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT * FROM orders WHERE email = ? ORDER BY created_at DESC");
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
