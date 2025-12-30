<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/newsletter.php';

/**
 * Obtener todos los posts
 */
function getAllPosts($conn = null): array {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $sql = "SELECT * FROM posts ORDER BY id DESC";
    $res = $conn->query($sql);

    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Obtener post por ID
 */
function getPostById(int $id, $conn = null): ?array {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    if (!$stmt) return null;

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $post ?: null;
}

/**
 * Crear post
 * NOTA: notify_subscribers controla si se envía email (excepcional)
 */
function addPost(
    string $title,
    string $short_description,
    string $content,
    ?string $image,
    ?string $link,
    bool $notify_subscribers = false,
    $conn = null
): bool {

    if ($conn === null) {
        $conn = connectToDatabase();
    }

    $stmt = $conn->prepare("
        INSERT INTO posts
        (title, short_description, content, image, link, notify_subscribers, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) return false;

    $notify = $notify_subscribers ? 1 : 0;

    $stmt->bind_param(
        "sssssi",
        $title,
        $short_description,
        $content,
        $image,
        $link,
        $notify
    );

    $ok = $stmt->execute();
    $post_id = $conn->insert_id;
    $stmt->close();

    if ($ok && $notify) {
        notifySubscribersAboutPost($post_id);
    }

    return $ok;
}

/**
 * Avisar a suscriptores sobre un post concreto
 * Uso excepcional, no automático
 */
function notifySubscribersAboutPost(int $post_id): void {

    $post = getPostById($post_id);
    if (!$post) return;

    $subscribers = getAllSubscribers();
    if (empty($subscribers)) return;

    $postUrl      = "https://alphasupps.alwaysdata.net/views/post.php?id={$post_id}";
    $safeUrl      = htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8');
    $title        = htmlspecialchars((string) ($post["title"] ?? "Nuevo post"), ENT_QUOTES, 'UTF-8');
    $short        = htmlspecialchars((string) ($post["short_description"] ?? ''), ENT_QUOTES, 'UTF-8');
    $image        = trim((string) ($post["image"] ?? ''));
    $safeImage    = $image ? htmlspecialchars($image, ENT_QUOTES, 'UTF-8') : '';
    $subject      = "Nuevo post en AlphaSupps: {$title}";

    $hero = '';
    if ($safeImage !== '') {
        $hero = "
      <div style='border-radius:12px;overflow:hidden;margin:14px 0 18px;border:1px solid rgba(255,255,255,0.08);'>
        <img src='{$safeImage}' alt='{$title}' style='width:100%;display:block;max-height:320px;object-fit:cover;background:#0f0f11;'>
      </div>";
    }

    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="es">
<body style="margin:0;padding:0;background:#0b0b0d;color:#f5f5f5;font-family:Inter,Arial,sans-serif;">
  <div style="max-width:640px;margin:30px auto;padding:0 16px;">
    <div style="background:#0d0d0f;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:28px 24px;box-shadow:0 18px 38px rgba(0,0,0,0.35);">
      <div style="text-align:center;margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" style="width:60px;height:60px;color:#f5d27a;">
          <g fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="50" cy="50" r="46"/>
            <path d="M50 18 L72 75 L28 75 Z"/>
            <path d="M47 32 C49 38, 53 42, 51 47 C49 52, 43 56, 46 61 L56 72"/>
          </g>
        </svg>
      </div>
      <p style="margin:0 0 6px;font-size:13px;letter-spacing:0.08em;color:#f5d27a;font-weight:700;text-transform:uppercase;">Nuevo post</p>
      <h2 style="margin:0 0 12px;font-size:26px;color:#fff;line-height:1.25;">{$title}</h2>
      <p style="margin:0 0 18px;font-size:16px;line-height:1.6;color:#dcdcdc;">{$short}</p>
      {$hero}
      <div style="margin:24px 0 18px;">
        <a href="{$safeUrl}" style="display:inline-block;padding:14px 22px;border-radius:10px;background:#f5d27a;color:#111;font-weight:700;text-decoration:none;box-shadow:0 8px 18px rgba(0,0,0,0.3);">
          Leer el post
        </a>
      </div>
      <div style="background:rgba(245,210,122,0.1);border:1px solid rgba(245,210,122,0.25);padding:14px 16px;border-radius:10px;margin:8px 0 20px;">
        <p style="margin:0;font-size:14px;line-height:1.5;color:#e8e8e8;">Consejos basados en ciencia y experiencia para tu rendimiento.</p>
      </div>
      <p style="margin:0;font-size:14px;line-height:1.5;color:#9fa2ac;">Recibes este email porque te apuntaste a las novedades de AlphaSupps.</p>
      <p style="margin:8px 0 0;font-size:13px;line-height:1.4;color:#7b7f89;">Si no quieres más avisos, responde a este email con \"baja\" y lo gestionamos.</p>
    </div>
  </div>
</body>
</html>
HTML;

    foreach ($subscribers as $sub) {
        sendMail($sub["email"], $subject, $htmlBody);
        sleep(1); // evita flags de spam
    }
}

/**
 * Actualizar post (NO dispara emails)
 */
function updatePost(
    int $id,
    string $title,
    string $short_description,
    string $content,
    ?string $image,
    ?string $link
): bool {

    $conn = connectToDatabase();

    $stmt = $conn->prepare("
        UPDATE posts
        SET title = ?, short_description = ?, content = ?, image = ?, link = ?
        WHERE id = ?
    ");

    if (!$stmt) return false;

    $stmt->bind_param(
        "sssssi",
        $title,
        $short_description,
        $content,
        $image,
        $link,
        $id
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Eliminar post
 */
function deletePost(int $id): bool {

    $conn = connectToDatabase();

    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Obtener posts paginados
 */
function getPosts(int $limit, int $page): array {

    $conn = connectToDatabase();
    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare(
        "SELECT * FROM posts ORDER BY id DESC LIMIT ? OFFSET ?"
    );

    if (!$stmt) return [];

    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

/**
 * Total de posts
 */
function getTotalPosts(): int {

    $conn = connectToDatabase();
    $res = $conn->query("SELECT COUNT(*) AS total FROM posts");

    return $res ? (int)$res->fetch_assoc()["total"] : 0;
}

/**
 * Subir imagen de post
 */
function uploadPostImage(): ?string {

    if (empty($_FILES['image_upload']['name'])) {
        return null;
    }

    $fileName  = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['image_upload']['name']);
    $uploadDir = __DIR__ . '/../images/posts/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $targetPath)) {
        return '/images/posts/' . $fileName;
    }

    return null;
}
