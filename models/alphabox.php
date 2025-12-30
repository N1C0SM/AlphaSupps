<?php

require_once __DIR__ . '/db.php';

/**
 * Obtiene una suscripción AlphaBox por email.
 */
function getAlphaBoxByEmail(string $email, $conn = null): ?array {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    $email = strtolower(trim($email));
    if ($email === '') return null;

    $stmt = $conn->prepare("SELECT * FROM alphabox WHERE LOWER(email) = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

/**
 * Obtiene una suscripción AlphaBox por ID.
 */
function getAlphaBoxById(int $id, $conn = null): ?array {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    if ($id <= 0) return null;

    $stmt = $conn->prepare("SELECT * FROM alphabox WHERE id = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

/**
 * Crea o actualiza una suscripción AlphaBox.
 *
 * @param array $data
 *   - user_id (int|null)
 *   - name (string)
 *   - email (string)
 *   - plan (string)
 *   - frequency (string)
 *   - notes (string|null)
 *   - items (array|null)
 *   - token (string) para confirmación
 */
function saveAlphaBox(array $data, $conn = null): array {
    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $userId    = $data['user_id'] ? (int) $data['user_id'] : null;
    $name      = trim((string) ($data['name'] ?? ''));
    $email     = strtolower(trim((string) ($data['email'] ?? '')));
    $plan      = trim((string) ($data['plan'] ?? 'basica'));
    $frequency = trim((string) ($data['frequency'] ?? 'mensual'));
    $notes     = trim((string) ($data['notes'] ?? ''));
    $items     = $data['items'] ?? [];
    $token     = trim((string) ($data['token'] ?? ''));

    if ($name === '' || $email === '') {
        return ["success" => false, "message" => "Faltan nombre o email."];
    }

    $itemsPayload = [
        "plan" => $plan,
        "frequency" => $frequency,
        "notes" => $notes,
        "items" => $items,
        "token" => $token
    ];
    $itemsJson = json_encode($itemsPayload);

    // Buscar existente por email (case-insensitive)
    $existing = getAlphaBoxByEmail($email, $conn);

    if ($existing) {
        $stmt = $conn->prepare("
            UPDATE alphabox
               SET user_id = ?, name = ?, plan = ?, frequency = ?, items_json = ?, status = 'pendiente',
                   notes = ?, updated_at = NOW(), next_confirmation_at = NULL
             WHERE id = ?
        ");
        if (!$stmt) {
            return ["success" => false, "message" => "Error al preparar la consulta."];
        }

        $stmt->bind_param(
            "isssssi",
            $userId,
            $name,
            $plan,
            $frequency,
            $itemsJson,
            $notes,
            $existing['id']
        );
        $ok = $stmt->execute();
        $stmt->close();

        return $ok
            ? ["success" => true, "message" => "Solicitud actualizada.", "id" => (int) $existing['id']]
            : ["success" => false, "message" => "No se pudo actualizar la solicitud."];
    }

    $stmt = $conn->prepare("
        INSERT INTO alphabox (user_id, name, email, plan, frequency, items_json, status, notes, next_confirmation_at, last_confirmed_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?, NULL, NULL, NOW(), NOW())
    ");
    if (!$stmt) {
        return ["success" => false, "message" => "Error al preparar la consulta."];
    }

    $stmt->bind_param(
        "issssss",
        $userId,
        $name,
        $email,
        $plan,
        $frequency,
        $itemsJson,
        $notes
    );
    $ok = $stmt->execute();
    $newId = $ok ? $stmt->insert_id : 0;
    $stmt->close();

    return $ok
        ? ["success" => true, "message" => "Solicitud creada.", "id" => (int) $newId]
        : ["success" => false, "message" => "No se pudo guardar la solicitud."];
}

/**
 * Confirma una suscripción AlphaBox usando token.
 */
function confirmAlphaBox(int $id, string $token, $conn = null): array {
    if ($conn === null) {
        $conn = connectToDatabase();
    }
    if ($id <= 0 || $token === '') {
        return ["success" => false, "message" => "Datos inválidos."];
    }

    $row = getAlphaBoxById($id, $conn);
    if (!$row) {
        return ["success" => false, "message" => "No encontramos esta solicitud."];
    }

    $payload = json_decode($row['items_json'] ?? '{}', true) ?: [];
    $storedToken = $payload['token'] ?? '';
    if ($storedToken === '' || !hash_equals($storedToken, $token)) {
        return ["success" => false, "message" => "Token de confirmación no válido."];
    }

    $next = computeNextDate($row['frequency']);

    $stmt = $conn->prepare("
        UPDATE alphabox
           SET status = 'activa',
               last_confirmed_at = NOW(),
               next_confirmation_at = ?,
               updated_at = NOW()
         WHERE id = ?
    ");
    if (!$stmt) {
        return ["success" => false, "message" => "No pudimos confirmar ahora."];
    }

    $stmt->bind_param("si", $next, $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok
        ? ["success" => true, "message" => "AlphaBox confirmada. Preparamos el siguiente paso."]
        : ["success" => false, "message" => "No se pudo actualizar el estado."];
}

/**
 * Calcula próxima fecha de confirmación según frecuencia.
 */
function computeNextDate(string $frequency): string {
    $now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
    switch ($frequency) {
        case 'cada_2_meses':
            $now->modify('+2 months');
            break;
        case 'cada_3_meses':
            $now->modify('+3 months');
            break;
        default:
            $now->modify('+1 month');
            break;
    }
    return $now->format('Y-m-d H:i:s');
}
