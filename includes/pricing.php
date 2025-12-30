<?php
/**
 * Cálculo centralizado de precios y stock.
 * - Precio final = precio base almacenado + margen configurable
 * - Margen distinto para productos, packs y suscripciones
 * - Suscripción aplica descuento adicional del 10%
 */

if (!function_exists('connectToDatabase')) {
    require_once __DIR__ . '/../models/db.php';
}

/**
 * Obtiene los márgenes desde variables de entorno con valores por defecto.
 */
function pricingMargins(): array {
    return [
        'product' => floatval(getenv('MARGIN_PRODUCT') ?: 0.12),
        'pack' => floatval(getenv('MARGIN_PACK') ?: 0.10),
        'subscription' => floatval(getenv('MARGIN_SUBSCRIPTION') ?: 0.15),
    ];
}

/**
 * Devuelve precio final para un suplemento (con o sin suscripción).
 */
function computeSupplementPrice(array $row, bool $isSubscription = false, bool $isPackContext = false): array {
    $margins = pricingMargins();
    $base = isset($row['price']) ? floatval($row['price']) : 0.0;

    if ($base <= 0) {
        return ['error' => 'Precio no disponible', 'debug' => $row];
    }

    if ($isSubscription) {
        $margin = isset($row['margin_subscription']) ? floatval($row['margin_subscription']) : $margins['subscription'];
    } elseif ($isPackContext) {
        $margin = isset($row['margin_pack']) ? floatval($row['margin_pack']) : $margins['pack'];
    } else {
        $margin = isset($row['margin_product']) ? floatval($row['margin_product']) : $margins['product'];
    }
    if ($margin < 0) {
        $margin = 0.0;
    }

    $final = $base + ($base * $margin);
    if ($isSubscription) {
        $final = $final * 0.9; // descuento de suscripción
    }

    return [
        'price' => round($final, 2),
        'base' => $base,
        'margin' => $margin,
        'source' => 'base_price'
    ];
}

/**
 * Calcula precio de pack personalizado con márgenes y descuento por nº de ítems.
 */
function computeCustomPackPrice(mysqli $conn, array $features): array {
    $ids = [];
    foreach ($features as $feature) {
        $id = intval($feature['id'] ?? 0);
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    $ids = array_values(array_unique($ids));
    if (!$ids) {
        return ['error' => 'Debes seleccionar al menos un producto válido'];
    }

    $idsSql = implode(',', array_map('intval', $ids));
    $result = $conn->query("SELECT id, name, price, margin_product, margin_subscription FROM supplements WHERE id IN ($idsSql)");
    if (!$result) {
        return ['error' => 'No se pudieron obtener los productos del pack'];
    }

    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[(int)$row['id']] = $row;
    }

    $resolved = [];
    $subtotal = 0.0;
    foreach ($features as $feature) {
        $id = intval($feature['id'] ?? 0);
        $qty = max(1, intval($feature['qty'] ?? 1));

        if ($id <= 0 || !isset($map[$id])) {
            return ['error' => 'Hay productos inválidos en el pack'];
        }

        $pricing = computeSupplementPrice($map[$id], false, true);
        if (!empty($pricing['error'])) {
            return ['error' => $pricing['error']];
        }

        $linePrice = $pricing['price'] * $qty;
        $subtotal += $linePrice;

        $resolved[] = [
            'id' => $id,
            'name' => $map[$id]['name'] ?? ($feature['name'] ?? ''),
            'price' => $pricing['price'],
            'qty' => $qty
        ];
    }

    $count = count($resolved);
    $discountRate = 0.0;
    if ($count >= 4) {
        $discountRate = 0.10;
    } elseif ($count === 3) {
        $discountRate = 0.07;
    } elseif ($count === 2) {
        $discountRate = 0.05;
    }

    $discount = $subtotal * $discountRate;
    $total = $subtotal - $discount;

    return [
        'features' => $resolved,
        'subtotal' => round($subtotal, 2),
        'discount_rate' => $discountRate,
        'total' => round($total, 2)
    ];
}
