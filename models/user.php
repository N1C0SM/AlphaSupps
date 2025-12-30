<?php
require_once '../models/db.php';

/**
 * Obtener todos los usuarios
 * Usado en el panel de administración
 */
function getAllUsers($conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $sql = "SELECT id, name, email, role, created_at FROM users ORDER BY id DESC";
    $result = $conn->query($sql);

    $users = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    return $users;
}

/**
 * Obtener usuario por ID
 */
function getUserById($id, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    return $user ?: null;
}

/**
 * Obtener usuario por email (para login)
 */
function getUserByEmail($email, $conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    return $user ?: null;
}

/**
 * Crear nuevo usuario (registro desde admin)
 */
function createUser($name, $email, $password, $role = 'user', $conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        return ['success' => false, 'message' => 'El correo ya está registrado.'];
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
    $success = $stmt->execute();

    $stmt->close();

    return $success
        ? ['success' => true, 'message' => 'Usuario creado con éxito.']
        : ['success' => false, 'message' => 'Error al registrar el usuario.'];
}

/**
 * Actualizar rol del usuario
 */
function updateUserRole($id, $role, $conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    $success = $stmt->execute();

    $stmt->close();

    return $success;
}

/**
 * Eliminar usuario por ID
 */
function deleteUser($id, $conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();

    $stmt->close();
    return $success;
}

/**
 * Registrar nuevo usuario (desde formulario público)
 */
function registerUser($name, $email, $password, $confirmPassword, $role = 'user', $conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }
    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Las contraseñas no coinciden.'];
    }


    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        return ['success' => false, 'message' => 'El correo ya está registrado.'];
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
    $success = $stmt->execute();

    $stmt->close();

    return $success
        ? ['success' => true, 'message' => 'Usuario registrado con éxito.']
        : ['success' => false, 'message' => 'Error al registrar el usuario.'];
}

/**
 * Login de usuario
 * Guarda todos los datos en sesión y devuelve info del usuario
 */
function loginUser($email, $password, $conn = null) {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $user = getUserByEmail($email, $conn);

    if (!$user) {
        return ['success' => false, 'message' => 'Usuario no encontrado.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Contraseña incorrecta.'];
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'login_time' => date('Y-m-d H:i:s')
    ];

    return [
        'success' => true,
        'message' => 'Inicio de sesión exitoso.',
        'user' => $_SESSION['user']
    ];
}

/**
 * Cerrar sesión del usuario
 */
function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_unset();
    session_destroy();

    return ['success' => true, 'message' => 'Sesión cerrada correctamente.'];
}
/**
 * Obtiene el número de usuarios registrados por mes en el año actual.
 *
 * @return array Cantidad de usuarios por mes (enero a diciembre).
 */
/**
 * Obtiene el número de usuarios registrados por mes en el año actual.
 *
 * @return array Cantidad de usuarios por mes (enero a diciembre).
 */
function getMonthlyUsers($conn = null) {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $sql = "
        SELECT MONTH(created_at) AS mes, COUNT(*) AS total
        FROM users
        WHERE YEAR(created_at) = YEAR(CURDATE())
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)
    ";

    $result = $conn->query($sql);
    $data = array_fill(0, 12, 0);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[(int)$row['mes'] - 1] = (int)$row['total'];
        }
    }

    return $data;
}

function updateUser($userId, $name, $email, $conn = null) {
  if ($conn === null) {
      $conn = connectToDatabase();
  }

  $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
  $stmt->bind_param("ssi", $name, $email, $userId);
  return $stmt->execute();
}
function updatePassword($userId, $currentPassword, $newPassword) {
  $conn = connectToDatabase();
  $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$row = $result->fetch_assoc()) return false;

  $hash = $row['password'];
  if (!password_verify($currentPassword, $hash)) return false;

  $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
  $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
  $update->bind_param("si", $newHash, $userId);
  return $update->execute();
}
function guardarSuscribers($email) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
    if (!$stmt) {
        return [
            "success" => false,
            "message" => "Error al preparar la consulta."
        ];
    }

    $stmt->bind_param("s", $email);
    $ok = $stmt->execute();

    if ($ok) {
        return [
            "success" => true,
            "message" => "Te has suscrito correctamente."
        ];
    }

    // Email duplicado
    if ($conn->errno === 1062) {
        return [
            "success" => false,
            "message" => "Este correo ya está suscrito."
        ];
    }

    // ERROR GENERAL
    return [
        "success" => false,
        "message" => "Error al guardar en la base de datos.",
        "debug" => $conn->error
    ];
}
function guardarRegistroPrelanzamiento($name, $email) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("
        INSERT INTO registros_prelanzamiento (name, email, register_date)
        VALUES (?, ?, NOW())
    ");

    if (!$stmt) {
        return [
            "success" => false,
            "message" => "Error al preparar la consulta."
        ];
    }

    $stmt->bind_param("ss", $name, $email);
    $ok = $stmt->execute();

    $errorCode = $conn->errno;

    $stmt->close();

    if ($ok) {
        return [
            "success" => true,
            "message" => "Registro exitoso."
        ];
    }

    if ($errorCode === 1062) {
        return [
            "success" => false,
            "message" => "Este correo ya está registrado."
        ];
    }

    return [
        "success" => false,
        "message" => "Error al guardar en la base de datos."
    ];
}
