<?php

require_once __DIR__ . '/db.php';

/**
 * Comprueba si un email ya existe en la lista de avisos.
 */
function subscriberExists(string $email): bool {

    $conn = connectToDatabase();
    if (!$conn) return false;

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM newsletter_subscribers WHERE email = ?"
    );

    if (!$stmt) return false;

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

/**
 * Añade un email a la lista de avisos.
 * No es una newsletter: solo avisos relevantes.
 */
function addSubscriber(string $email): bool {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Si ya existe, no hacemos nada (experiencia tranquila)
    if (subscriberExists($email)) {
        return true;
    }

    $conn = connectToDatabase();
    if (!$conn) return false;

    $stmt = $conn->prepare(
        "INSERT INTO newsletter_subscribers (email) VALUES (?)"
    );

    if (!$stmt) return false;

    $stmt->bind_param("s", $email);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) return false;

    // Email de confirmación tranquilo (opcional pero recomendado)
    sendSubscriberConfirmationEmail($email);

    return true;
}

/**
 * Email de confirmación sin marketing ni presión.
 */
function sendSubscriberConfirmationEmail(string $email): bool {

    $subject = "Te avisaremos cuando haya algo relevante";

    $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#ffffff;color:#111;">
  <div style="max-width:600px;margin:40px auto;padding:0 20px;">
    <p>Ey,</p>

    <p>has dejado tu email para recibir avisos de AlphaSupps.</p>

    <p>
      No enviamos newsletters ni promociones constantes.<br>
      Solo te escribiremos cuando haya algo realmente relevante.
    </p>

    <p>
      Si en algún momento no te interesa, puedes ignorar el mensaje o darte de baja sin problema.
    </p>

    <p style="margin-top:30px;">
      AlphaSupps
    </p>
  </div>
</body>
</html>
HTML;

    return sendMail($email, $subject, $html);
}

/**
 * Devuelve todos los emails de la lista de avisos.
 */
function getAllSubscribers(): array {

    $conn = connectToDatabase();
    if (!$conn) return [];

    $rows = [];

    $res = $conn->query(
        "SELECT id, email, subscribed_at
         FROM newsletter_subscribers
         ORDER BY subscribed_at DESC"
    );

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $res->free();
    }

    return $rows;
}

/**
 * Elimina un suscriptor por ID.
 */
function deleteSubscriber(int $id): bool {

    $conn = connectToDatabase();
    if (!$conn) return false;

    $stmt = $conn->prepare(
        "DELETE FROM newsletter_subscribers WHERE id = ?"
    );

    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Obtiene un suscriptor por email.
 */
function getSubscriberByEmail(string $email): ?array {

    $conn = connectToDatabase();
    if (!$conn) return null;

    $stmt = $conn->prepare(
        "SELECT * FROM newsletter_subscribers WHERE email = ?"
    );

    if (!$stmt) return null;

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;

    $stmt->close();

    return $row;
}