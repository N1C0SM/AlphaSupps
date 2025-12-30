<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../cache/cache.php';

/**
 * Obtener un suplemento por ID
 */
function getSupplementById($id) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM supplements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $supplement = $result->num_rows ? $result->fetch_assoc() : null;
    $stmt->close();
    return $supplement;
}

/**
 * Obtener todos los suplementos (con paginación opcional y cache)
 */
function getAllSupplements($limit = null, $offset = null, $useCache = true) {
    global $cache;
    $key = 'supplements_' . ($limit ?? 'all') . '_' . ($offset ?? '0');

    $fetchSupplements = function() use ($limit, $offset) {
        $conn = connectToDatabase();

        $sql = "SELECT * FROM supplements ORDER BY created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $result = $conn->query($sql);

        $supplements = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $supplements[] = $row;
            }
        }
        return $supplements;
    };

    if (!$useCache) {
        return $fetchSupplements();
    }

    return $cache->get($key, $fetchSupplements, 1800); // Cache 30 minutos
}

/**
 * Obtener suplementos por marca
 */
function getSupplementsByBrand($brandId) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM supplements WHERE idBrand = ?");
    $stmt->bind_param("i", $brandId);
    $stmt->execute();
    $result = $stmt->get_result();

    $supplements = [];
    while ($row = $result->fetch_assoc()) {
        $supplements[] = $row;
    }
    $stmt->close();
    return $supplements;
}

/**
 * Crear suplemento (con imágenes, beneficios y estudios JSON)
 */
function createSupplement($name, $description, $price, $brandId, $collectionId, $imagesJson, $benefitsJson, $studiesJson = null) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("
        INSERT INTO supplements (name, description, price, idBrand, idCollection, images, benefits, studies, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssdiisss", $name, $description, $price, $brandId, $collectionId, $imagesJson, $benefitsJson, $studiesJson);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        clearCacheOnUpdate('supplement');
    }
    return $ok;
}

/**
 * Actualizar suplemento
 */
function updateSupplement($id, $name, $description, $price, $brandId, $collectionId, $imagesJson, $benefitsJson, $studiesJson = null) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("
        UPDATE supplements
        SET name=?, description=?, price=?, idBrand=?, idCollection=?, images=?, benefits=?, studies=?
        WHERE id=?
    ");
    $stmt->bind_param("ssdiisssi", $name, $description, $price, $brandId, $collectionId, $imagesJson, $benefitsJson, $studiesJson, $id);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        clearCacheOnUpdate('supplement');
    }
    return $ok;
}

/**
 * Eliminar suplemento
 */
function deleteSupplement($id) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("DELETE FROM supplements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        clearCacheOnUpdate('supplement');
    }
    return $ok;
}

/**
 * Suplementos por mes (dashboard)
 */
function getMonthlySupplements() {
    $conn = connectToDatabase();

    $sql = "
        SELECT MONTH(created_at) AS mes, COUNT(*) AS total
        FROM supplements
        WHERE YEAR(created_at) = YEAR(CURDATE())
        GROUP BY MONTH(created_at)
        ORDER BY mes
    ";

    $result = $conn->query($sql);
    $data = array_fill(0, 12, 0);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[(int)$row['mes'] - 1] = (int)$row['total'];
        }
    }
    return $data;
}
