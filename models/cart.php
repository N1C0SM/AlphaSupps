<?php
require_once __DIR__ . '/db.php';

function getUserCart($conn = null, $userId = 0) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $sql = "SELECT cart FROM users WHERE id = $userId";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    return $row["cart"] ? json_decode($row["cart"], true) : [];
}

function saveUserCart($conn = null, $userId = 0, $jsonCart = '') {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $esc = $conn->real_escape_string($jsonCart);
    $sql = "UPDATE users SET cart='$esc' WHERE id=$userId";
    return $conn->query($sql);
}

function mergeCarts($tempCart, $dbCart) {
    foreach ($tempCart as $name => $item) {
        if (isset($dbCart[$name])) {
            $dbCart[$name]['qty'] = max($dbCart[$name]['qty'], $item['qty']);
        } else {
            $dbCart[$name] = $item;
        }
    }
    return $dbCart;
}
