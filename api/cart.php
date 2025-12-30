<?php
session_start();
header("Content-Type: application/json");

require_once "../models/db.php";
$conn = connectToDatabase();

if (!isset($_SESSION["user"])) {
    echo json_encode(["error" => "not_logged"]);
    exit;
}

$user_id = $_SESSION["user"]["id"];
$action = $_GET["action"] ?? null;

if (!$action) {
    echo json_encode(["error" => "missing action"]);
    exit;
}

/*----------------------------------------------------
  GET: Obtener carrito desde users.cart
-----------------------------------------------------*/
if ($action === "get") {
    $stmt = $conn->prepare("SELECT cart FROM users WHERE id = ?");
    if (!$stmt) {
        echo json_encode(["sql_error" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode([]);
        exit;
    }

    $stmt->bind_result($cart_json);
    $stmt->fetch();

    echo $cart_json ?: json_encode([]);
    exit;
}

/*----------------------------------------------------
  SAVE: Guardar carrito en users.cart
-----------------------------------------------------*/
if ($action === "save") {
    $json = file_get_contents("php://input");

    if (!$json) {
        echo json_encode(["error" => "empty cart"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET cart = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(["sql_error" => $conn->error]);
        exit;
    }

    $stmt->bind_param("si", $json, $user_id);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

/*----------------------------------------------------
  MERGE: Fusionar carrito invitado con users.cart
-----------------------------------------------------*/
if ($action === "merge") {
    $guest_json = file_get_contents("php://input");
    $guest_cart = json_decode($guest_json, true) ?? [];

    $stmt = $conn->prepare("SELECT cart FROM users WHERE id = ?");
    if (!$stmt) {
        echo json_encode(["sql_error" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    $current_cart = [];

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_json);
        $stmt->fetch();

        if ($db_json) {
            $current_cart = json_decode($db_json, true) ?? [];
        }
    }

    foreach ($guest_cart as $product => $item) {
        if (isset($current_cart[$product])) {
            $current_cart[$product]["qty"] += $item["qty"];
        } else {
            $current_cart[$product] = $item;
        }
    }

    $final_json = json_encode($current_cart);

    $stmt = $conn->prepare("UPDATE users SET cart = ? WHERE id = ?");
    $stmt->bind_param("si", $final_json, $user_id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "merged_cart" => $current_cart
    ]);
    exit;
}

echo json_encode(["error" => "invalid action"]);