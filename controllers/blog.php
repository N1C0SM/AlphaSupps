<?php
require_once '../models/db.php';
require_once '../models/post.php';
$isAdminPanel = false;
$conn = connectToDatabase();
$posts = getAllPosts($conn);

if (isset($_GET['id'])) {
    $post = getPostById($_GET['id'], $conn);
    if (!$post) {
        header("HTTP/1.0 404 Not Found");
        exit("Post no encontrado");
    }
}
