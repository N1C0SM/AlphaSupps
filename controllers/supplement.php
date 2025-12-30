<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/supplement.php';
require_once __DIR__ . '/../models/brand.php';
require_once __DIR__ . '/../models/reviews.php';


// ============================
// 🔹 VALIDAR ID
// ============================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die('ID de suplemento inválido.');
}

$id = (int) $_GET['id'];

// ============================
// 🔹 OBTENER DATOS DEL SUPLEMENTO
// ============================
$conn = connectToDatabase();
$supplement = getSupplementById($id);

if (!$supplement) {
  die('Suplemento no encontrado.');
}

// Obtener nombre de la marca
$supplement['brand_name'] = getBrandNameById($supplement['idBrand'] ?? 0);

// ============================
// 🔹 VARIABLES SEO DINÁMICAS
// ============================

// ============================
// 🔹 DATOS PARA LA VISTA
// ============================
$supplement['images'] = json_decode($supplement['images'] ?? '[]', true) ?: [];
$supplement['benefits'] = json_decode($supplement['benefits'] ?? '[]', true) ?: [];
$supplement['features'] = json_decode($supplement['features'] ?? '[]', true) ?: [];
$supplement['items'] = json_decode($supplement['items'] ?? '[]', true) ?: [];
$supplement['studies'] = json_decode($supplement['studies'] ?? '[]', true) ?: [];
$images = $supplement['images'];
$benefits = $supplement['benefits'];
$mainImage = $images[0] ?? '';
$price = (float) ($supplement['price'] ?? 0);

// ============================
// 🔹 reviews ASOCIADOS
// ============================
$reviews = getReviewsBySuplement($conn, $id, 4);
