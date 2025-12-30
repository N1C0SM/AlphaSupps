<?php
require_once '../models/post.php';
require_once '../models/newsletter.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Controlador principal de posts.
 */
$action = $_POST['action'] ?? $_GET['action'] ?? null;
if (!$action) exit("Acción no válida.");

switch ($action) {
    case 'add':    handleAddPost();    break;
    case 'update': handleUpdatePost(); break;
    case 'delete': handleDeletePost(); break;
    case 'get':    handleGetPost();    break;
    case 'list':   handleListPosts();  break;
    default: exit("Acción no válida.");
}


/* ===========================================================
   CREAR POST
   =========================================================== */
   function handleAddPost(): void {
    checkAdmin();

    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $link = trim($_POST['link'] ?? '');

    // Imagen desde input URL
    $image = trim($_POST['image'] ?? '');

    // SUBIDA REAL DE IMAGEN
    $upload = uploadPostImage();
    if ($upload) {
        $image = $upload;
    }

    if (!$title || !$short_description || !$content) {
        exit("Faltan campos obligatorios.");
    }

    $ok = addPost($title, $short_description, $content, $image, $link);

    if (!$ok) {
        exit("Error al crear el post.");
    }

    echo "OK";
}



/* ===========================================================
   ACTUALIZAR POST
   =========================================================== */
function handleUpdatePost(): void {
    checkAdmin();

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) exit("ID inválido.");

    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $link = trim($_POST['link'] ?? '');

    // URL existente
    $image = trim($_POST['image'] ?? '');

    // Imagen nueva opcional
    $upload = uploadPostImage();
    if ($upload) {
        $image = $upload;
    }

    if (!$title || !$short_description || !$content) {
        exit("Datos inválidos.");
    }

    $ok = updatePost($id, $title, $short_description, $image, $link, $content);

    if ($ok) {
        echo "OK";
    } else {
        exit("Error al actualizar el post.");
    }
}



/* ===========================================================
   ELIMINAR POST
   =========================================================== */
function handleDeletePost(): void {
    checkAdmin();

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) exit("ID inválido.");

    $ok = deletePost($id);

    if ($ok) {
        echo "OK";
    } else {
        exit("Error al eliminar el post.");
    }
}



/* ===========================================================
   OBTENER POST INDIVIDUAL
   =========================================================== */
function handleGetPost(): void {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) exit("ID inválido.");

    $post = getPostById($id);
    if (!$post) exit("Post no encontrado.");

    header('Content-Type: application/json');
    echo json_encode($post, JSON_UNESCAPED_UNICODE);
}



/* ===========================================================
   LISTAR POSTS (JSON)
   =========================================================== */
function handleListPosts(): void {
    $posts = getAllPosts();

    header('Content-Type: application/json');
    echo json_encode($posts, JSON_UNESCAPED_UNICODE);
}



/* ===========================================================
   AUXILIARES
   =========================================================== */

function checkAdmin(): void {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        exit("No autorizado.");
    }
}

?>