<?php
require_once '../models/post.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de post inválido.");
}

$id = (int) $_GET['id'];
$post = getPostById($id);

if (!$post) {
    die("Post no encontrado.");
}
?>
<!doctype html>
<html lang="es">
  <?php require_once '../components/head.php'; ?>
  <?php require_once '../components/cookies.php'; ?>
  <body>
    <?php require_once '../components/header.php'; ?>
    <main class="post-page">
      <div class="post">
        <a href="./index.php" class="back-btn">← Volver al Blog</a>
        <h2><?= htmlspecialchars($post['title']) ?></h2>

        <?php if (!empty($post['image'])): ?>
          <img loading="lazy" src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        <?php endif; ?>

        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      </div>
    </main>
    <?php require_once '../components/footer.php'; ?>
  </body>
</html>