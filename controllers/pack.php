<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/pack.php';
require_once __DIR__ . '/../includes/pricing.php';
require_once __DIR__ . '/../services/ai/openai-image.php';

$conn = connectToDatabase();
/**
 * Helper to redirect and end execution.
 */
function redirectAndExit(string $path, array $params = []): void {
    $query = $params ? '?' . http_build_query($params) : '';
    header('Location: ' . $path . $query);
    exit;
}

/**
 * Helper to send JSON responses and stop script.
 */
function jsonResponse(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

/**
 * Decode JSON form fields safely.
 */
function decodeJsonField($value, $default = []): array {
    if (empty($value)) {
        return $default;
    }

    $decoded = json_decode($value, true);
    return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $default;
}

function calculatePackDiscountRate(int $count): float {
    if ($count >= 4) {
        return 0.10;
    }
    if ($count === 3) {
        return 0.07;
    }
    if ($count === 2) {
        return 0.05;
    }
    return 0.0;
}

function buildCustomPackPricing(mysqli $conn, array $features): array {
    // Delegamos en el motor de pricing centralizado (incluye márgenes Amazon)
    return computeCustomPackPrice($conn, $features);
}

/**
 * Build shareable pack URL.
 */
function buildPackShareUrl(int $packId, ?string $token = null): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = '/views/pack.php?id=' . $packId;

    if ($token) {
        $path .= '&token=' . urlencode($token);
    }

    return rtrim($scheme . '://' . $host, '/') . $path;
}

/**
 * Store uploaded image (if any) and build images payload for DB.
 */
function buildImagePayload(?string $autoPrompt = null): array {
    $defaultImage = '../images/hero-900.webp';
    $images = [];
    $source = $_POST['image_source'] ?? 'url';

    if ($source === 'file' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../images/packs/custom/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $originalName = $_FILES['image_file']['name'] ?? 'pack.png';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            $extension = 'jpg';
        }

        try {
            $random = bin2hex(random_bytes(3));
        } catch (Exception $e) {
            $random = time();
        }

        $fileName = 'custom_pack_' . time() . '_' . $random . '.' . $extension;
        $destination = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destination)) {
            $images[] = '../images/packs/custom/' . $fileName;
        }
    }

    if (empty($images)) {
        $imageUrl = trim($_POST['image_url'] ?? '');
        if ($imageUrl !== '') {
            $images[] = $imageUrl;
        }
    }

    // Generación automática si no se aportó imagen y hay prompt
    if (empty($images) && $autoPrompt) {
        try {
            $generator = new OpenAIImageGenerator();
            // Forzamos randomize para evitar imágenes repetidas aunque AI_IMAGE_AUTO_VARIATION esté desactivado
            $result = $generator->generate($autoPrompt, ['n' => 1, 'randomize' => true]);
            if (!empty($result['images'][0])) {
                $images[] = $result['images'][0];
            }
        } catch (Throwable $e) {
            // Silenciar y usar fallback si falla la IA
        }
    }

    if (empty($images)) {
        $images[] = $defaultImage;
    }

    return $images;
}

/**
 * Handle creation of private packs from the custom pack form.
 */
function handleCreatePrivatePack(mysqli $conn): void {
    if (!isset($_SESSION['user']['id'])) {
        redirectAndExit('../views/login.php', ['redirect' => 'custom-pack']);
    }

    $ownerId = intval($_SESSION['user']['id']);
    $features = decodeJsonField($_POST['features'] ?? '[]');

    if (empty($features)) {
        redirectAndExit('../views/custom-pack.php', [
            'status' => 'error',
            'message' => 'Debes seleccionar al menos un producto para crear tu pack'
        ]);
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'Personalizado';
    $benefits = decodeJsonField($_POST['benefits'] ?? '[]');
    $label = trim($_POST['label'] ?? 'Personalizado') ?: 'Personalizado';

    $pricing = buildCustomPackPricing($conn, $features);
    if (!empty($pricing['error'])) {
        redirectAndExit('../views/custom-pack.php', [
            'status' => 'error',
            'message' => $pricing['error']
        ]);
    }

    $features = $pricing['features'] ?? $features;
    $price = floatval($pricing['total'] ?? 0);

    if ($price <= 0) {
        redirectAndExit('../views/custom-pack.php', [
            'status' => 'error',
            'message' => 'No se pudo calcular el precio del pack'
        ]);
    }

    $featureNames = [];
    foreach ($features as $f) {
        if (is_array($f) && !empty($f['name'])) {
            $qty = isset($f['qty']) ? max(1, (int)$f['qty']) : null;
            $featureNames[] = trim(($qty ? $qty . 'x ' : '') . $f['name']);
        }
    }
    $prompt = null;
    if ($featureNames) {
        $prompt = "Pack de suplementos \"" . ($name ?: 'Personalizado') . "\" con: " . implode(', ', array_slice($featureNames, 0, 6)) . ". Foto lifestyle premium, fondo limpio, iluminación realista, estilo AlphaSupps.";
    }

    $images = buildImagePayload($prompt);
    $result = insertPack(
        $conn,
        $ownerId,
        $name ?: 'Pack Personalizado',
        $description,
        $images,
        $benefits,
        $price,
        $category,
        'private',
        $features,
        $label,
        null
    );

    if (!is_int($result)) {
        redirectAndExit('../views/custom-pack.php', [
            'status' => 'error',
            'message' => $result ?: 'No se pudo crear el pack'
        ]);
    }

    redirectAndExit('../views/pack.php', [
        'id' => $result,
        'created' => 1
    ]);
}

/**
 * Handle AJAX visibility changes.
 */
function handleChangeVisibility(mysqli $conn): void {
    if (!isset($_SESSION['user']['id'])) {
        jsonResponse(['success' => false, 'message' => 'Debes iniciar sesión'], 401);
    }

    $packId = intval($_POST['pack_id'] ?? 0);
    $visibility = $_POST['visibility'] ?? '';
    $allowed = ['public', 'private', 'shared_link', 'pending_public', 'rejected_public'];

    if ($packId <= 0 || !in_array($visibility, $allowed, true)) {
        jsonResponse(['success' => false, 'message' => 'Datos inválidos'], 400);
    }

    $pack = getPackById($conn, $packId);
    if (!$pack) {
        jsonResponse(['success' => false, 'message' => 'Pack no encontrado'], 404);
    }

    $userId = intval($_SESSION['user']['id']);
    $ownerId = intval($pack['owner_id'] ?? 0);
    $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

    if ($ownerId !== $userId && !$isAdmin) {
        jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
    }

    $dbVisibility = in_array($visibility, $allowed, true) ? $visibility : 'private';

    $linkPayload = null;
    $shareUrl = null;

    $customLink = trim($_POST['link'] ?? '');
    $customLink = filter_var($customLink, FILTER_VALIDATE_URL) ? $customLink : '';
    $feedback = trim($_POST['feedback'] ?? '');
    $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    $requestedPublish = ($_POST['request_publish'] ?? '') === '1';

    if ($visibility === 'public') {
        if ($isAdmin) {
            $shareUrl = $customLink ?: buildPackShareUrl($packId);
            $linkPayload = null; // no token cuando es público
            $dbVisibility = 'public';
        } else {
            // Usuario pide publicar: guardamos solicitud en link y mantenemos privado
            $dbVisibility = 'private';
            $linkPayload = [
                'publish_request' => [
                    'user_id' => $_SESSION['user']['id'] ?? null,
                    'note' => $feedback,
                    'created_at' => date('c')
                ]
            ];
            $shareUrl = null;
        }
    } elseif ($visibility === 'shared_link') {
        try {
            $token = bin2hex(random_bytes(12));
        } catch (Exception $e) {
            $token = bin2hex(random_bytes(6));
        }

        $shareUrl = $customLink ?: buildPackShareUrl($packId, $token);
        $linkPayload = [
            'token' => $token,
            'share_url' => $shareUrl,
            'created_at' => date('c')
        ];
    } elseif ($visibility === 'rejected_public') {
        if (!$isAdmin) {
            jsonResponse(['success' => false, 'message' => 'Solo un admin puede rechazar publicación'], 403);
        }
        $dbVisibility = 'private';
        $linkPayload = [
            'publish_feedback' => [
                'admin_id' => $_SESSION['user']['id'] ?? null,
                'note' => $feedback,
                'created_at' => date('c')
            ]
        ];
        $shareUrl = null;
    } else {
        // private: invalidar cualquier enlace previo
        $linkPayload = null;
        $shareUrl = null;
        $dbVisibility = 'private';
    }

    $linkJson = $linkPayload ? json_encode($linkPayload) : null;

    $stmt = $conn->prepare("UPDATE packs SET visibility = ?, link = ?, updated_at = NOW() WHERE id = ?");
    if (!$stmt) {
        jsonResponse(['success' => false, 'message' => 'No se pudo preparar la consulta'], 500);
    }

    $stmt->bind_param("ssi", $dbVisibility, $linkJson, $packId);

    if (!$stmt->execute()) {
        jsonResponse(['success' => false, 'message' => 'No se pudo actualizar la visibilidad'], 500);
    }

    jsonResponse([
        'success' => true,
        'visibility' => $dbVisibility,
        'share_url' => $shareUrl,
        'feedback' => $feedback
    ]);
}

// -------------------------------------------------
// POST (creación del pack personalizado)
// -------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'change_visibility') {
        handleChangeVisibility($conn);
    }

    if ($action === 'create_private_pack') {
        handleCreatePrivatePack($conn);
    }

    // Fallback según tipo de petición
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($acceptHeader, 'application/json') !== false) {
        jsonResponse(['success' => false, 'message' => 'Acción no reconocida'], 400);
    }

    redirectAndExit('../views/custom-pack.php', [
        'status' => 'error',
        'message' => 'Acción no reconocida'
    ]);
}

// -------------------------------------------------
// GET (detalle de un pack existente)
// -------------------------------------------------
$packId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$packId) {
    redirectAndExit('../views/packs.php', [
        'status' => 'error',
        'message' => 'Pack no encontrado'
    ]);
}

$pack = getPackById($conn, $packId);

if (!$pack) {
    require_once __DIR__ . '/../views/404.php';
    exit;
}

// -------------------------------------------------
// VISIBILIDAD: validar acceso a pack
// -------------------------------------------------
$userId   = $_SESSION['user']['id'] ?? null;
$userRole = $_SESSION['user']['role'] ?? null;
$isAdmin  = $userRole === 'admin';
$isOwner  = $userId && intval($userId) === intval($pack['owner_id'] ?? 0);
$visibility = $pack['visibility'] ?? 'private';
$tokenParam = $_GET['token'] ?? null;
$linkData = is_array($pack['link'] ?? null) ? $pack['link'] : null;
$linkToken = $linkData['token'] ?? null;

$unauthorized = false;

if ($visibility === 'private' && !$isOwner && !$isAdmin) {
    $unauthorized = true;
}

if ($visibility === 'shared_link' && !$isOwner && !$isAdmin) {
    if (!$linkToken || !$tokenParam || $tokenParam !== $linkToken) {
        $unauthorized = true;
    }
}

if ($unauthorized) {
    require_once __DIR__ . '/../views/404.php';
    exit;
}

// Si no es público, marcar flag para desactivar rating en la vista
$canRate = $visibility === 'public';
