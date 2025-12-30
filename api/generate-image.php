<?php
require_once __DIR__ . '/../config.php'; // Inicia sesión + env
require_once __DIR__ . '/../services/ai/openai-image.php';

header('Content-Type: application/json');

function respond(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// Solo administradores para evitar abuso de la API
if (!isset($_SESSION['user'])) {
    respond(['success' => false, 'error' => 'auth_required'], 401);
}
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    respond(['success' => false, 'error' => 'forbidden'], 403);
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    $data = $_POST; // fallback si no viene JSON
}

$prompt = trim($data['prompt'] ?? '');
if ($prompt === '') {
    respond(['success' => false, 'error' => 'prompt_required'], 400);
}

// Opciones opcionales para ajustar el resultado
$options = [];
foreach (['model', 'size', 'style', 'quality', 'prompt_prefix', 'prompt_suffix', 'seed', 'randomize', 'response_format'] as $field) {
    if (isset($data[$field])) {
        $options[$field] = $data[$field];
    }
}
if (isset($data['n'])) {
    $options['n'] = $data['n'];
}

try {
    $generator = new OpenAIImageGenerator();
    $result = $generator->generate($prompt, $options);

    respond([
        'success' => true,
        'prompt_used' => $result['prompt'],
        'model' => $result['model'],
        'size' => $result['size'],
        'count' => $result['count'],
        'images' => $result['images']
    ]);
} catch (InvalidArgumentException $e) {
    respond(['success' => false, 'error' => $e->getMessage()], 400);
} catch (RuntimeException $e) {
    respond(['success' => false, 'error' => $e->getMessage()], 500);
}
