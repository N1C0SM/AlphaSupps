<?php
require_once __DIR__ . '/db.php'; // Debe definir $conn = new mysqli(...);

function addReview($conn, $comentario, $autor, $idSuplement, $rating, $userId = null) {
  $stmt = $conn->prepare("INSERT INTO reviews (coment, author, idSuplement, rating, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
  if (!$stmt) {
    error_log("addReview prepare error: " . $conn->error);
    return false;
  }
  $stmt->bind_param("ssiii", $comentario, $autor, $idSuplement, $rating, $userId);
  $ok = $stmt->execute();
  if (!$ok) {
    error_log("addReview execute error: " . $stmt->error);
  }
  $stmt->close();
  return $ok;
}

function getAllReviews($conn) {
  $sql = "
    SELECT r.*, s.name AS supplement_name
    FROM reviews r
    JOIN supplements s ON r.idSuplement = s.id
    ORDER BY r.id DESC
  ";
  $result = $conn->query($sql);
  $reviews = [];
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $reviews[] = $row;
    }
  }
  return $reviews;
}

function deleteReview($conn, $id) {
  $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}
function getReviewsLimit($conn, $limit) {

  // Aseguramos que el límite sea un número entero positivo
  $limit = intval($limit);
  if ($limit <= 0) $limit = 5; // por defecto 5 si se pasa algo inválido

  $sql = "
    SELECT r.*, s.name AS supplement_name
    FROM reviews r
    JOIN supplements s ON r.idSuplement = s.id
    ORDER BY r.id DESC
    LIMIT {$limit}
  ";

  $result = $conn->query($sql);

  $reviews = [];
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $reviews[] = $row;
    }
  }
  return $reviews;
}
function getReviewsBySuplement($conn, $idSuplement, $limit = null) {

  // Asegurar que el ID sea un entero
  $idSuplement = intval($idSuplement);

  // Base del SQL
  $sql = "
    SELECT r.*, s.name AS supplement_name
    FROM reviews r
    JOIN supplements s ON r.idSuplement = s.id
    WHERE r.idSuplement = ?
    ORDER BY r.id DESC
  ";

  // Agregar el límite si se pasa
  if ($limit !== null) {
    $limit = intval($limit);
    $sql .= " LIMIT {$limit}";
  }

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    return [];
  }

  $stmt->bind_param("i", $idSuplement);
  $stmt->execute();
  $result = $stmt->get_result();

  $reviews = [];
  while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
  }

  $stmt->close();
  return $reviews;
}

function getReviewsByPack($conn, $idPack, $limit = null) {

  // Asegurar que el ID sea un entero
  $idPack = intval($idPack);

  // Base del SQL
  $sql = "
    SELECT r.*, p.name AS pack_name
    FROM reviews r
    JOIN packs p ON r.idPack = p.id
    WHERE r.idPack = ?
    ORDER BY r.id DESC
  ";

  // Agregar el límite si se pasa
  if ($limit !== null) {
    $limit = intval($limit);
    $sql .= " LIMIT {$limit}";
  }

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    return [];
  }

  $stmt->bind_param("i", $idPack);
  $stmt->execute();
  $result = $stmt->get_result();

  $reviews = [];
  while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
  }

  $stmt->close();
  return $reviews;
}

function addPackReview($conn, $comentario, $autor, $idPack, $rating, $userId = null) {
  $stmt = $conn->prepare("INSERT INTO reviews (coment, author, idPack, rating, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
  if (!$stmt) {
    error_log("addPackReview prepare error: " . $conn->error);
    return false;
  }
  $stmt->bind_param("ssiii", $comentario, $autor, $idPack, $rating, $userId);
  $success = $stmt->execute();
  if (!$success) {
    error_log("addPackReview execute error: " . $stmt->error);
  }
  $stmt->close();
  return $success;
}

function updatePackReview($conn, $reviewId, $comentario, $rating) {
  $stmt = $conn->prepare("UPDATE reviews SET coment = ?, rating = ? WHERE id = ?");
  if (!$stmt) {
    error_log("updatePackReview prepare error: " . $conn->error);
    return false;
  }
  $stmt->bind_param("sii", $comentario, $rating, $reviewId);
  $success = $stmt->execute();
  if (!$success) {
    error_log("updatePackReview execute error: " . $stmt->error);
  }
  $stmt->close();
  return $success;
}

function updateSupplementReview($conn, $reviewId, $comentario, $rating) {
  $stmt = $conn->prepare("UPDATE reviews SET coment = ?, rating = ? WHERE id = ?");
  if (!$stmt) {
    error_log("updateSupplementReview prepare error: " . $conn->error);
    return false;
  }
  $stmt->bind_param("sii", $comentario, $rating, $reviewId);
  $success = $stmt->execute();
  if (!$success) {
    error_log("updateSupplementReview execute error: " . $stmt->error);
  }
  $stmt->close();
  return $success;
}

function getUserReviewForPack($conn, $userId, $packId) {
  $stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND idPack = ?");
  if (!$stmt) {
    error_log("getUserReviewForPack prepare error: " . $conn->error);
    return null;
  }

  $stmt->bind_param("ii", $userId, $packId);

  if (!$stmt->execute()) {
    error_log("getUserReviewForPack execute error: " . $stmt->error);
    $stmt->close();
    return null;
  }

  $result = $stmt->get_result();
  $review = $result->num_rows ? $result->fetch_assoc() : null;
  $stmt->close();
  return $review;
}

function getUserReviewForSupplement($conn, $userId, $supplementId) {
  $stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND idSuplement = ?");
  if (!$stmt) {
    error_log("getUserReviewForSupplement prepare error: " . $conn->error);
    return null;
  }

  $stmt->bind_param("ii", $userId, $supplementId);

  if (!$stmt->execute()) {
    error_log("getUserReviewForSupplement execute error: " . $stmt->error);
    $stmt->close();
    return null;
  }

  $result = $stmt->get_result();
  $review = $result->num_rows ? $result->fetch_assoc() : null;
  $stmt->close();
  return $review;
}
