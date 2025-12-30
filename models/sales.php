<?php
require_once __DIR__ . '/db.php'; // connectToDatabase()

/**
 * Resumen total de ventas y pedidos pagados
 */
function getSalesSummary() {
    $conn = connectToDatabase();

    // Total € de ventas pagadas
    $queryTotal = "
        SELECT SUM(price) AS total
        FROM orders
        WHERE payment_id IS NOT NULL AND payment_id <> ''
    ";

    // Total pedidos pagados
    $queryCount = "
        SELECT COUNT(*) AS count
        FROM orders
        WHERE payment_id IS NOT NULL AND payment_id <> ''
    ";

    $totalSales = 0;
    $totalOrders = 0;

    if ($result = $conn->query($queryTotal)) {
        $row = $result->fetch_assoc();
        $totalSales = (float)($row['total'] ?? 0);
        $result->close();
    }

    if ($result = $conn->query($queryCount)) {
        $row = $result->fetch_assoc();
        $totalOrders = (int)($row['count'] ?? 0);
        $result->close();
    }

    return [
        'totalSales'  => $totalSales,
        'totalOrders' => $totalOrders
    ];
}

/**
 * Ventas mensuales agrupadas por año-mes
 */
function getMonthlySales() {
    $conn = connectToDatabase();

    $query = "
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(price) AS total
        FROM orders
        WHERE payment_id IS NOT NULL AND payment_id <> ''
        GROUP BY month
        ORDER BY month ASC
    ";

    $result = $conn->query($query);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[$row['month']] = (float)$row['total'];
    }

    if (count($data) === 1) {
        $onlyMonth = array_keys($data)[0];
        $prevMonth = date('Y-m', strtotime("$onlyMonth -1 month"));
        $data = [$prevMonth => 0] + $data;
    }

    return $data;
}