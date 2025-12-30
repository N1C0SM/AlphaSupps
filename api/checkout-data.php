<?php
session_start();
header('Content-Type: application/json');

$loggedIn = isset($_SESSION['user']['id']);

if (!$loggedIn) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $data = $_SESSION['checkout_data'] ?? [];
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payload inválido']);
        exit;
    }

    $allowed = ['name', 'email', 'address', 'postal_code', 'phone'];
    $clean = [];
    foreach ($allowed as $key) {
        if (isset($input[$key])) {
            $clean[$key] = trim((string)$input[$key]);
        }
    }
    $_SESSION['checkout_data'] = $clean;

    echo json_encode(['success' => true, 'data' => $clean]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método no permitido']);
