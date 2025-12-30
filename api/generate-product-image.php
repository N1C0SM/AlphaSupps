<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../services/ai/openai-image.php';

header('Content-Type: application/json');

function respondProdImage(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// Solo admin para evitar abuso
if (!isset($_SESSION['user'])) {
    respondProdImage(['success' => false, 'error' => 'auth_required'], 401);
}
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    respondProdImage(['success' => false, 'error' => 'forbidden'], 403);
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    $data = $_POST;
}

$name = trim($data['name'] ?? '');
$description = trim($data['description'] ?? '');
if ($name === '') {
    respondProdImage(['success' => false, 'error' => 'name_required'], 400);
}

$promptPieces = [$name];
if ($description !== '') {
    $promptPieces[] = $description;
}
$prompt = implode('. ', $promptPieces);

// Opciones opcionales
$options = [];
foreach (['model','size','style','quality','seed','randomize','response_format'] as $field) {
    if (isset($data[$field])) {
        $options[$field] = $data[$field];
    }
}

try {
    $generator = new OpenAIImageGenerator();
    $result = $generator->generate($prompt, $options);

    respondProdImage([
        'success' => true,
        'prompt_used' => $result['prompt'],
        'model' => $result['model'],
        'size' => $result['size'],
        'count' => $result['count'],
        'images' => $result['images']
    ]);
} catch (InvalidArgumentException $e) {
    respondProdImage(['success' => false, 'error' => $e->getMessage()], 400);
} catch (RuntimeException $e) {
    respondProdImage(['success' => false, 'error' => $e->getMessage()], 500);
}
