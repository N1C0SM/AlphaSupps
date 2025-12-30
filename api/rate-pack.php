<?php
session_start();
header('Content-Type: application/json');

require_once '../models/db.php';
require_once '../models/pack.php';


// ------------------------------------------------------
// Funciones auxiliares
// ------------------------------------------------------

function leerEntrada() {
    $raw = file_get_contents("php://input");
    return json_decode($raw, true);
}

function errorJson($msg) {
    echo json_encode(["success" => false, "message" => $msg]);
    exit;
}

function okJson($data = []) {
    echo json_encode(array_merge(["success" => true], $data));
    exit;
}

function validarSesion() {
    if (!isset($_SESSION['user']['id'])) {
        errorJson("Debes iniciar sesión para realizar esta acción");
    }
    return $_SESSION['user']['id'];
}


// ------------------------------------------------------
// Acciones
// ------------------------------------------------------

function accionCalificarPack($input) {
    $userId = validarSesion();

    $packId = intval($input['pack_id'] ?? 0);
    $rating = intval($input['rating'] ?? 0);

    if ($packId <= 0 || $rating < 1 || $rating > 5) {
        errorJson("Datos inválidos");
    }

    $conn = connectToDatabase();

    $pack = getPackById($conn, $packId);
    if (!$pack) {
        errorJson("Pack no encontrado");
    }

    $success = updatePackRating($conn, $packId, $rating);

    if ($success) {
        okJson([
            "message" => "Valoración actualizada correctamente",
            "newRating" => $rating
        ]);
    }

    errorJson("Error al actualizar la valoración");
}


// ------------------------------------------------------
// Flujo principal con SWITCH
// ------------------------------------------------------

$input = leerEntrada();

if (!isset($input["action"])) {
    errorJson("No se recibió ninguna acción");
}

switch ($input["action"]) {

    case "calificar":
        accionCalificarPack($input);
        break;

    default:
        errorJson("Acción no reconocida");
}