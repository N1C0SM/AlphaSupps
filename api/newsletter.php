<?php
require_once __DIR__ . '/../models/newsletter.php';
header('Content-Type: application/json');

// ------------------------------------------------------
// Funciones auxiliares
// ------------------------------------------------------

function jsonError($msg) {
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

function jsonOK($data = []) {
    echo json_encode(array_merge(['status' => 'success'], $data));
    exit;
}

function leerPost($key, $default = null) {
    return $_POST[$key] ?? $default;
}


// ------------------------------------------------------
// Acciones
// ------------------------------------------------------

function accionSubscribe() {
    $email = trim(leerPost('email', ''));

    if (empty($email)) {
        jsonError('El correo es obligatorio.');
    }

    if (subscriberExists($email)) {
        echo json_encode(['status' => 'exists', 'message' => 'Este correo ya está suscrito.']);
        exit;
    }

    $ok = addSubscriber($email);

    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok
            ? '¡Gracias por suscribirte! Te enviamos un correo de bienvenida.'
            : 'No se pudo procesar tu suscripción. Inténtalo nuevamente.'
    ]);
    exit;
}

function accionDelete() {
    $id = intval(leerPost('id', 0));

    $ok = deleteSubscriber($id);

    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'Suscriptor eliminado.' : 'No se pudo eliminar.'
    ]);
    exit;
}

function accionListSubscribers() {
    $subscribers = getAllSubscribers();
    echo json_encode($subscribers);
    exit;
}


// ------------------------------------------------------
// SWITCH GENERAL
// ------------------------------------------------------

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // --------------------------------------------------
    // PETICIONES POST (suscribir / borrar)
    // --------------------------------------------------
    case 'POST':
        $action = leerPost('action');

        if (!$action) {
            jsonError('No se especificó ninguna acción.');
        }

        switch ($action) {

            case 'subscribe':
                accionSubscribe();
                break;

            case 'delete':
                accionDelete();
                break;

            default:
                jsonError('Acción no válida.');
        }
        break;


    // --------------------------------------------------
    // PETICIONES GET (listar suscriptores)
    // --------------------------------------------------
    case 'GET':
        accionListSubscribers();
        break;


    // --------------------------------------------------
    default:
        jsonError('Método no permitido.');
}

?>