<?php
require_once '../models/db.php'; // Tus funciones: connectDatabase() y getOrderById()
require_once '../models/user.php'; // Tus funciones: connectDatabase() y getOrderById()

// --- OBTENER ID DEL PEDIDO ---
$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    die("ID de pedido no proporcionado.");
}

// --- CONEXIÓN A LA BASE DE DATOS ---
$pdo = connectToDatabase();

// --- OBTENER PEDIDO ---
$order = getOrderById($orderId);
if (!$order) {
    die("Pedido no encontrado.");
}

// --- PREPARAR DATOS DE FACTURA ---
$numeroFactura = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$fechaFactura = date("d/m/Y", strtotime($order['created_at']));

$cliente = [
    'name' => $order['name'],
    'email' => $order['email'],
    'address' => $order['address'],
    'postal_code' => $order['postal_code'],
    'phone' => $order['phone']
];

$productos = [
    [
        'nombre' => $order['product'],
        'cantidad' => $order['quantity'],
        'precio' => $order['price']
    ]
];

// --- CALCULAR SUBTOTAL, IVA Y TOTAL ---
$subtotal = 0;
foreach ($productos as $producto) {
    $subtotal += $producto['cantidad'] * $producto['precio'];
}
$iva = round($subtotal * 0.21, 2); // IVA 21%
$total = round($subtotal + $iva, 2);