<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../models/db.php';

function readJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!$input) {
        parse_str($raw, $input);
    }
    return is_array($input) ? $input : [];
}

function respondError(string $message): void
{
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function respondOk(array $data): void
{
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

$input = readJsonInput();
$orderId = isset($input['order_id']) ? intval($input['order_id']) : 0;
$email = isset($input['email']) ? trim((string)$input['email']) : '';

if ($orderId <= 0 || $email === '') {
    respondError('Faltan datos: indica ID de pedido y email.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondError('Email inválido.');
}

$conn = connectToDatabase();
if (!$conn) {
    respondError('No se pudo conectar a la base de datos.');
}

$stmt = $conn->prepare("
    SELECT id, email, fulfillment_status, tracking_code, tracking_url, created_at, product, price
    FROM orders
    WHERE id = ? AND email = ?
    LIMIT 1
");

if (!$stmt) {
    respondError('No se pudo preparar la consulta.');
}

$stmt->bind_param('is', $orderId, $email);
$stmt->execute();
$res = $stmt->get_result();
$order = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$order) {
    respondError('No se encontró un pedido con esos datos.');
}

respondOk([
    'order' => [
        'id' => (int)$order['id'],
        'product' => $order['product'],
        'price' => floatval($order['price']),
        'fulfillment_status' => $order['fulfillment_status'] ?? 'pending',
        'tracking_code' => $order['tracking_code'] ?? null,
        'tracking_url' => $order['tracking_url'] ?? null,
        'created_at' => $order['created_at'] ?? null,
    ]
]);
