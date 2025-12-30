<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/user.php';


$conn = connectToDatabase();

// ============================
// 🔹 VARIABLES SEO
// ============================
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

// ============================
// 🔹 REDIRECCIÓN SI YA ESTÁ LOGUEADO
// ============================
if (isset($_SESSION['user'])) {
  header('Location: ../views/index.php');
  exit;
}
