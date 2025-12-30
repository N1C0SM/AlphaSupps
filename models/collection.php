<?php
require_once __DIR__ . '/db.php';

/**
 * Obtiene todas las colecciones con sus suplementos asociados.
 *
 * @return array Lista de colecciones con sus suplementos.
 */
function getAllCollections($conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    if (!$conn) {
        die("Error de conexión a la base de datos: " . mysqli_connect_error());
    }

    $collectionsQuery = "SELECT * FROM collections";
    $collectionsResult = $conn->query($collectionsQuery);

    $collectionsData = [];

    if ($collectionsResult && $collectionsResult->num_rows > 0) {
        while ($collectionRow = $collectionsResult->fetch_assoc()) {

            $collectionId = (int)$collectionRow['id'];


            $stmt = $conn->prepare("SELECT * FROM supplements WHERE idCollection = ? LIMIT 3");
            $stmt->bind_param("i", $collectionId);
            $stmt->execute();
            $supplementsResult = $stmt->get_result();

            $supplements = [];
            if ($supplementsResult && $supplementsResult->num_rows > 0) {
                while ($supplementRow = $supplementsResult->fetch_assoc()) {
                    $supplements[] = $supplementRow;
                }
            }

            $stmt->close();

            $collectionRow['supplements'] = $supplements;
            $collectionsData[] = $collectionRow;
        }
    }

    return $collectionsData;
}

/**
 * Obtiene todos los suplementos pertenecientes a una colección específica.
 *
 * @param int $collectionId ID de la colección.
 * @return array Lista de suplementos de esa colección.
 */
function getSupplementsByCollectionId($collectionId, $conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    if (!$conn) {
        die("Error de conexión a la base de datos: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("SELECT * FROM supplements WHERE idCollection = ?");
    $stmt->bind_param("i", $collectionId);
    $stmt->execute();

    $result = $stmt->get_result();
    $supplements = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $supplements[] = $row;
        }
    }

    $stmt->close();

    return $supplements;
}

/**
 * Obtiene una colección por su ID.
 *
 * @param int $id ID de la colección.
 * @return array|null Datos de la colección o null si no existe.
 */
function getCollectionById($id, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $stmt = $conn->prepare("SELECT * FROM collections WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $collection = $result->fetch_assoc() ?: null;
    $stmt->close();
    return $collection;
}

/**
 * Crea una nueva colección.
 *
 * @param mysqli $connection Conexión a la base de datos.
 * @param string $name Nombre de la colección.
 * @param string $description Descripción de la colección.
 * @return bool Éxito o fracaso.
 */
function createCollection($name, $description, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $stmt = $conn->prepare("INSERT INTO collections (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Actualiza los datos de una colección.
 *
 * @param mysqli $connection Conexión a la base de datos.
 * @param int $id ID de la colección.
 * @param string $name Nombre actualizado.
 * @param string $description Descripción actualizada.
 * @return bool Éxito o fracaso.
 */
function updateCollection($id, $name, $description, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $stmt = $conn->prepare("UPDATE collections SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Elimina una colección por su ID.
 *
 * @param mysqli $connection Conexión a la base de datos.
 * @param int $id ID de la colección a eliminar.
 * @return bool Éxito o fracaso.
 */
function deleteCollection($id, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $stmt = $conn->prepare("DELETE FROM collections WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>
