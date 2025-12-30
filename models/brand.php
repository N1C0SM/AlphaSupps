<?php
require_once __DIR__ . '/db.php';

/**
 * Obtiene todas las marcas disponibles en la base de datos.
 *
 * @return array Lista de marcas (cada una como array asociativo con id y nombre).
 */
function getBrands() {
    $conn = connectToDatabase();

    if (!$conn) {
        die("Error de conexión a la base de datos: " . mysqli_connect_error());
    }

    $query = "SELECT * FROM brands";
    $result = $conn->query($query);

    $brands = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }
    }

    return $brands;
}

/**
 * Devuelve el nombre de una marca a partir de su ID.
 *
 * @param int $idBrand ID de la marca.
 * @return string Nombre de la marca o "Desconocida" si no existe.
 */
function getBrandNameById($idBrand) {
    $conn = connectToDatabase();

    if (!$conn) {
        die("Error de conexión a la base de datos: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("SELECT name FROM brands WHERE id = ?");
    $stmt->bind_param("i", $idBrand);
    $stmt->execute();
    $result = $stmt->get_result();

    $brandName = "Desconocida";
    if ($row = $result->fetch_assoc()) {
        $brandName = $row['name'];
    }

    $stmt->close();

    return $brandName;
}
function getAllBrands() {
   $conn = connectToDatabase();
  $result = $conn->query("SELECT * FROM brands ORDER BY id DESC");
  return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function addBrand($name) {
   $conn = connectToDatabase();
  $stmt = $conn->prepare("INSERT INTO brands (name) VALUES (?)");
  $stmt->bind_param("s", $name);
  return $stmt->execute();
}
function deleteBrand($id) {
  $conn = connectToDatabase();
  $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
  $stmt->bind_param("i", $id);
  return $stmt->execute();
}
function updateBrand($id, $name) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare(" UPDATE brands SET name = ? WHERE id = ? ");
    $stmt->bind_param("si", $name, $id);
    return $stmt->execute();
}
?>
