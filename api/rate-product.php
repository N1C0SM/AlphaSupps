<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/pack.php';
require_once __DIR__ . '/../models/supplement.php';
require_once __DIR__ . '/../models/reviews.php';

// ------------------------------------------------------
// Funciones auxiliares
// ------------------------------------------------------

function leerEntrada() {
    $raw = file_get_contents("php://input");
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    return $decoded;
}

// Asegura que se use ruta absoluta para includes en CLI/testing
if (!function_exists('connectToDatabase')) {
    require_once __DIR__ . '/../models/db.php';
    require_once __DIR__ . '/../models/pack.php';
    require_once __DIR__ . '/../models/supplement.php';
    require_once __DIR__ . '/../models/reviews.php';
}
function errorJson($msg) {
    if (function_exists('fastcgi_finish_request')) {
        ob_clean();
    } else {
        @ob_clean();
    }
    echo json_encode(["success" => false, "message" => $msg]);
    exit;
}

function okJson($data = []) {
    if (function_exists('fastcgi_finish_request')) {
        ob_clean();
    } else {
        @ob_clean();
    }
    echo json_encode(array_merge(["success" => true], $data));
    exit;
}

// Maneja errores fatales para no devolver HTML
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && !headers_sent()) {
        http_response_code(500);
        @ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Error interno del servidor'
        ]);
    }
});

function validarSesion() {
    if (!isset($_SESSION['user']['id'])) {
        errorJson("Debes iniciar sesión para realizar esta acción");
    }
    return $_SESSION['user']['id'];
}

// ------------------------------------------------------
// Funciones de rating
// ------------------------------------------------------

function calcularRatingPromedio($reviews) {
    if (empty($reviews)) return 0;

    $total = 0;
    foreach ($reviews as $review) {
        $total += $review['rating'];
    }
    return round($total / count($reviews), 1);
}

function actualizarRatingPack($conn, $packId) {
    // Obtener todas las reviews del pack
    $reviews = getReviewsByPack($conn, $packId);
    $avgRating = calcularRatingPromedio($reviews);

    // Actualizar el rating promedio en la tabla packs
    $stmt = $conn->prepare("UPDATE packs SET rating = ? WHERE id = ?");
    $stmt->bind_param("di", $avgRating, $packId);
    $success = $stmt->execute();
    $stmt->close();

    return $success ? $avgRating : null;
}

function actualizarRatingSupplement($conn, $supplementId) {
    // Obtener todas las reviews del supplement
    $reviews = getReviewsBySuplement($conn, $supplementId);
    $avgRating = calcularRatingPromedio($reviews);

    // Actualizar el rating promedio en la tabla supplements
    $stmt = $conn->prepare("UPDATE supplements SET rating = ? WHERE id = ?");
    $stmt->bind_param("di", $avgRating, $supplementId);
    $success = $stmt->execute();
    $stmt->close();

    return $success ? $avgRating : null;
}

// ------------------------------------------------------
// Acciones
// ------------------------------------------------------

function accionCalificarProducto($input) {
    $userId = validarSesion();

    $productId = intval($input['product_id'] ?? 0);
    $rating = intval($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');
    $tipo = $input['tipo'] ?? 'supplement'; // 'pack' o 'supplement'

    if ($productId <= 0 || $rating < 1 || $rating > 5) {
        errorJson("Datos inválidos");
    }

    $conn = connectToDatabase();

    // Verificar que el producto existe
    if ($tipo === 'pack') {
        $product = getPackById($conn, $productId);
        if (!$product) {
            errorJson("Pack no encontrado");
        }
        if (($product['visibility'] ?? 'private') !== 'public') {
            errorJson("Solo se pueden valorar packs publicados");
        }
    } else {
        $product = getSupplementById($productId);
        if (!$product) {
            errorJson("Suplemento no encontrado");
        }
    }

    // Verificar si el usuario ya ha hecho una review
    $existingReview = null;
    if ($tipo === 'pack') {
        $existingReview = getUserReviewForPack($conn, $userId, $productId);
    } else {
        $existingReview = getUserReviewForSupplement($conn, $userId, $productId);
    }

    // Obtener nombre del usuario
    $userName = $_SESSION['user']['name'] ?? 'Usuario Anónimo';

    if ($existingReview) {
        // Actualizar review existente
        if ($tipo === 'pack') {
            $success = updatePackReview($conn, $existingReview['id'], $comment, $rating);
        } else {
            $success = updateSupplementReview($conn, $existingReview['id'], $comment, $rating);
        }
    } else {
        // Crear nueva review
        if ($tipo === 'pack') {
            $success = addPackReview($conn, $comment, $userName, $productId, $rating, $userId);
        } else {
            $success = addReview($conn, $comment, $userName, $productId, $rating, $userId);
        }
    }

    if (!$success) {
        errorJson("Error al guardar la valoración");
    }

    // Actualizar rating promedio
    $newAvg = null;
    if ($tipo === 'pack') {
        $newAvg = actualizarRatingPack($conn, $productId);
        updateHighlightedPackFromVotes($conn);
    } else {
        $newAvg = actualizarRatingSupplement($conn, $productId);
    }

    okJson([
        "message" => "Valoración guardada correctamente",
        "newRating" => $newAvg ?? $rating
    ]);
}

// ------------------------------------------------------
// Flujo principal
// ------------------------------------------------------

$input = leerEntrada();

if (!isset($input["action"])) {
    errorJson("No se recibió ninguna acción");
}

switch ($input["action"]) {
    case "rate":
        accionCalificarProducto($input);
        break;

    default:
        errorJson("Acción no reconocida");
}
