<?php require_once '../controllers/blog.php'; ?>
<!DOCTYPE html>
<html lang="es">
  <?php require_once '../components/head.php'; ?>
  <?php require_once '../components/cookies.php';?>
  <body>
    <?php require_once '../components/header.php'; ?>
    <main class="blog-page">
    <section class="hero">
  <h1>AlphaSupps Blog</h1>
  <p>Encuentra guías, consejos y las últimas novedades del mundo fitness y la suplementación.</p>
</section>
      <?php if (!empty($posts)): ?>
        <div class="blog-container">
          <?php foreach ($posts as $p): ?>
            <div class="post">
            <article class="post">
              <?php if (!empty($p['image'])): ?>
                <img loading="lazy" src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
              <?php endif; ?>
              <h2><?= htmlspecialchars($p['title']) ?></h2>
              <p><?= substr(strip_tags($p['short_description']), 0, 150) ?></p>
              <a href="./post.php?id=<?=$p['id'] ?>" class="cta-btn">Leer más</a>
            </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align:center; color:var(--text-light);">No hay publicaciones todavía.</p>
      <?php endif; ?>
      <div class="pagination">
        <!-- Ejemplo de paginación si la implementas -->
        <!-- <a href="?page=1">1</a> <strong>2</strong> <a href="?page=3">3</a> -->
      </div>
    </main>
    <?php require_once '../components/footer.php'; ?>
  </body>
</html>
