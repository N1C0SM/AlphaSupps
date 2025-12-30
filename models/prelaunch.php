<?php
require_once __DIR__ . '/db.php';

/**
 * Obtiene todos los registros de pre-lanzamiento.
 *
 * @return array Lista de registros o un array vacío.
 */
function getAllPrelaunchRegisters(): array {
    $conn = connectToDatabase();
    if (!$conn) return [];

    $result = $conn->query("SELECT * FROM registros_prelanzamiento ORDER BY id DESC");

    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
    }

    return $rows;
}

/**
 * Registra un nuevo usuario para el pre-lanzamiento.
 *
 * @param string $nombre
 * @param string $email
 * @return bool True si se insertó correctamente, false si no.
 */
function addPrelaunchRegister(string $nombre, string $email): array {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Correo electrónico no válido."];
    }

    $conn = connectToDatabase();
    if (!$conn) return ["success" => false, "message" => "Error de conexión a la base de datos."];

    $stmt = $conn->prepare("INSERT INTO registros_prelanzamiento (name, email, register_date) VALUES (?, ?, NOW())");
    if (!$stmt) {
        return ["success" => false, "message" => "Error al preparar la consulta."];
    }

    $stmt->bind_param("ss", $nombre, $email);
    $ok = $stmt->execute();

    $stmt->close();

    if ($ok) {
        return ["success" => true, "message" => "Registro exitoso. ¡Gracias por tu interés!"];
    } else {
        return ["success" => false, "message" => "Error al registrar. Inténtalo de nuevo."];
    }
}

/**
 * Elimina un registro de pre-lanzamiento por ID.
 *
 * @param int $id
 * @return bool
 */
function deletePrelaunchRegister(int $id): bool {
    $conn = connectToDatabase();
    if (!$conn) return false;

    $stmt = $conn->prepare("DELETE FROM registros_prelanzamiento WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();

    $stmt->close();

    return $ok;
}
function getPrelaunchByEmail($email) {
    $conn = connectToDatabase();

    $email = strtolower(trim((string)$email));
    if ($email === '') return null;

    $stmt = $conn->prepare("SELECT * FROM registros_prelanzamiento WHERE LOWER(email) = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

/**
 * Obtiene el número total de registros de pre-lanzamiento.
 *
 * @return int Número de registros.
 */
function getPrelaunchCount(): int {
    $conn = connectToDatabase();
    if (!$conn) return 0;

    $result = $conn->query("SELECT COUNT(*) as count FROM registros_prelanzamiento");

    if ($result && $row = $result->fetch_assoc()) {
        return (int) $row['count'];
    }

    return 0;
}
