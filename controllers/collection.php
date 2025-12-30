<?php

require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/supplement.php';
require_once __DIR__ . '/../models/brand.php';
require_once __DIR__ . '/../models/collection.php';
$conn = connectToDatabase();


$brands = getBrands();
$brandMap = [];
foreach ($brands as $brand) {
  $brandMap[$brand['id']] = $brand['name'];
}
$collectionId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
$collection = getCollectionById($collectionId, $conn);

$suplements = getAllSupplements();
