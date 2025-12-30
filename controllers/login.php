<?php

// ===========================
// LOGIN CONTROLLER
// ===========================

// Mensajes que lleguen por GET (opcional)
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

require_once __DIR__ . '/../models/db.php';
$conn = connectToDatabase();

// ===========================
// 🔹 VARIABLES SEO
// ===========================


// ===========================
// 🔹 REDIRECCIÓN SI YA ESTÁ LOGUEADO
// ===========================
if (isset($_SESSION['user'])) {

    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: ../views/panel.php');
        exit;
    }

    header('Location: ../views/index.php');
    exit;
}

// ===========================
// 🔹 MANEJO DE ERRORES (si hubo intento fallido)
// ===========================
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>