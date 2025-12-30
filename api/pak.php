<?php
require_once '../models/db.php';
require_once '../models/pack.php';

$conn = connectToDatabase();

// --- LISTADO DE PACKS (render vista) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    session_start();
    $userId = $_SESSION['user']['id'] ?? null;

    $publicPacks  = getPublicPacks($conn);
    $privatePacks = $userId ? getPacksByOwner($conn, $userId) : [];

    require '../views/packs/packs.view.php';
    exit;
}


// --- ENDPOINT: CAMBIAR VISIBILIDAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'change_visibility'
) {
    $packId     = $_POST['pack_id'] ?? null;
    $visibility = $_POST['visibility'] ?? null;
    $link       = $_POST['link'] ?? null;

    if (!$packId || !$visibility) {
        echo json_encode(["success" => false, "message" => "Missing fields"]);
        exit;
    }

    $ok = updatePackVisibility($conn, $packId, $visibility, $link);

    echo json_encode([
        "success" => $ok,
        "share_url" => ($visibility === 'public' || $visibility === 'shared_link')
            ? "/views/pack.php?id=" . $packId
            : null
    ]);
    exit;
}

echo "Invalid request";
