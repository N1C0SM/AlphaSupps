<?php
require_once __DIR__ . '/db.php';
require_once '../models/user.php';
require_once '../models/supplement.php';
require_once '../models/order.php';
require_once '../models/post.php';

/**
 * Función reutilizable para obtener un valor simple (COUNT, SUM, etc.)
 */
function fetchValue($conn, $query) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $res = $conn->query($query);

    if (!$res) {
        die("SQL ERROR: " . $conn->error . "<br>Query: $query");
    }

    $row = $res->fetch_row();
    return $row ? $row[0] : 0;
}

/**
 * MÉTRICAS PRINCIPALES DEL DASHBOARD
 */
function getTotals($conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $users       = getAllUsers($conn);
    $posts       = getAllPosts($conn);
    $orders      = getAllOrders($conn);
    $supplements = getAllSupplements();

    $totalUsers        = count($users);
    $totalPosts        = count($posts);
    $totalSupplements  = count($supplements);
    $totalOrders       = count($orders);

    $pendingOrders     = getPendingOrdersCount($conn);
    $completedOrders   = getCompletedOrdersCount($conn);

    $totalRevenue = 0;
    foreach ($orders as $order) {
        if (isset($order['total']))  $totalRevenue += floatval($order['total']);
        if (isset($order['amount'])) $totalRevenue += floatval($order['amount']);
        if (isset($order['price']))  $totalRevenue += floatval($order['price']);
    }

    return [
        'totalUsers'       => $totalUsers,
        'totalPosts'       => $totalPosts,
        'totalSupplements' => $totalSupplements,
        'totalOrders'      => $totalOrders,
        'pendingOrders'    => $pendingOrders,
        'completedOrders'  => $completedOrders,
        'totalSales'       => $totalRevenue
    ];
}





function getPendingOrdersCount($conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    return fetchValue(
        $conn,
        "SELECT COUNT(*) FROM orders
         WHERE has_sent = 0"
    );
}

// Completados
function getCompletedOrdersCount($conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    return fetchValue(
        $conn,
        "SELECT COUNT(*) FROM orders
         WHERE has_sent = 1"
    );
}
