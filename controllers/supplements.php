<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/supplement.php';
require_once __DIR__ . '/../models/brand.php';


$conn = connectToDatabase();

// ============================
// 🔹 VARIABLES SEO
// ============================

// ============================
// 🔹 PAGINACIÓN
// ============================
$perPage = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// ============================
// 🔹 DATOS DE MARCAS Y SUPLEMENTOS
// ============================
$brands = getBrands();
$brandMap = [];
foreach ($brands as $brand) {
  $brandMap[$brand['id']] = $brand['name'];
}

$totalSupplements = count(getAllSupplements($perPage, $offset));
$totalPages = ceil($totalSupplements / $perPage);

$supplements = getAllSupplements($perPage, $offset);

