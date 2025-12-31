<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$user = $_SESSION['user'] ?? null;
$admin = $user && (($user['role'] ?? '') === 'admin');

require_once __DIR__ . '/../config/seo.php';
require_once __DIR__ . '/../controllers/settings.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../controllers/config.php';
require_once __DIR__ . '/../models/db.php';

$path = $_SERVER['SCRIPT_FILENAME'] ?? '';
$currentPage = $path ? basename($path, '.php') : '';

$pageData = null;
foreach ($pages as $group) {
  if (isset($group[$currentPage])) {
    $pageData = $group[$currentPage];
    break;
  }
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = rtrim($scheme . '://' . $host, '/');
$canonicalPath = $pageData['canonical'] ?? ($_SERVER['REQUEST_URI'] ?? '/');
$canonicalUrl = $baseUrl . '/' . ltrim($canonicalPath, '/');

$hasSubscriptions = $hasSubscriptions ?? false;
if (!$hasSubscriptions && $user && isset($user['id'])) {
  $connHead = connectToDatabase();
  if ($connHead) {
    $uidHead = (int)$user['id'];
    $resHead = $connHead->query("SELECT 1 FROM subscriptions WHERE user_id = {$uidHead} LIMIT 1");
    if ($resHead && $resHead->num_rows > 0) {
      $hasSubscriptions = true;
    }
  }
}

$title          = $pageData['title']        ?? 'AlphaSupps';
$description    = $pageData['description']  ?? 'Tienda de suplementos AlphaSupps: calidad y ciencia para tu rendimiento.';
$keywords       = $pageData['keywords']     ?? '';
$uniqueFavicon  = $viewData['uniqueFavicon'] ?? 'favicon.svg';
$ogImage        = $baseUrl . '/images/' . $uniqueFavicon;
$pageCss        = $pageCss ?? [];
