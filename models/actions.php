<?php
require_once '../models/user.php'; // Asegúrate de que la ruta sea correcta

// Iniciar sesión (solo si no existe)
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Verifica si se ha enviado alguna acción
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    echo "No se especificó ninguna acción.";
    exit;
}

$action = $_POST['action'] ?? $_GET['action'];

// ============================================================================
// LOGIN
// ============================================================================
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header("Location: ../views/login.php?error=campos_vacios");
        exit;
    }

    $result = loginUser($email, $password);

    if ($result['success']) {
        // Si es admin, redirige al panel
        if ($_SESSION['user']['role'] === 'admin') {
            header("Location: ../../../views/index.php");
        } else {
            header("Location: ../../views/index.php");
        }
        exit;
    } else {
        header("Location: ../views/login.php?error=" . urlencode($result['message']));
        exit;
    }
}

// ============================================================================
// REGISTRO DE USUARIO
// ============================================================================
if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        header("Location: ../views/register.php?error=campos_vacios");
        exit;
    }

    $result = registerUser($name, $email, $password, $confirmPassword);

    if ($result['success']) {
        header("Location: ../views/login.php?success=" . urlencode($result['message']));
    } else {
        header("Location: ../views/register.php?error=" . urlencode($result['message']));
    }
    exit;
}

// ============================================================================
// LOGOUT
// ============================================================================
if ($action === 'logout') {
    logoutUser();
    header("Location: ../views/login.php?logout=1");
    exit;
}

// ============================================================================
// ACTUALIZAR ROL (solo admin)
// ============================================================================
if ($action === 'update_role') {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        echo "No tienes permiso para realizar esta acción.";
        exit;
    }

    $id = intval($_POST['id'] ?? 0);
    $role = trim($_POST['role'] ?? '');

    if ($id <= 0 || empty($role)) {
        echo "Datos inválidos.";
        exit;
    }

    $success = updateUserRole($id, $role);

    header("Location: ../views/admin.php?updated=" . ($success ? "1" : "0"));
    exit;
}

// ============================================================================
// ELIMINAR USUARIO (solo admin)
// ============================================================================
if ($action === 'delete_user') {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        echo "No tienes permiso para realizar esta acción.";
        exit;
    }

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo "ID inválido.";
        exit;
    }

    $success = deleteUser($id);
    header("Location: ../views/admin.php?deleted=" . ($success ? "1" : "0"));
    exit;
}

// ============================================================================
// SI LA ACCIÓN NO EXISTE
// ============================================================================
echo "Acción no válida.";
exit;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $content = $_POST['content'];
    $image = $_POST['image'] ?? '';
    $author_id = $_SESSION['user']['id'];

    if (addPost($title, $short_description, $content, $image, $author_id)) {
        header('Location: ../views/admin_posts.php?success=1');
        exit;
    } else {
        echo "Error al crear el post.";
    }
}