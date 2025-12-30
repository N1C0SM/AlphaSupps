<?php require_once '../controllers/supplements.php'; ?>
<!doctype html>
<html lang="es">
<?php require_once '../components/head.php'; ?>
<?php require_once '../components/cookies.php'; ?>
<body>
<?php require_once '../components/header.php'; ?>
<main class="supplements-page">
<section class="hero">
  <h1>Cada suplemento con su ficha y referencias visibles</h1>
  <p class="section-subtitle">
    Compartimos datos públicos y referencias cuando están disponibles. Sin cifras infladas ni promesas de resultados; contrasta siempre con tu profesional de confianza.
  </p>
</section>
<p class="section-subtitle">
  Incluimos enlaces a estudios o fichas técnicas cuando existen y lo indicamos cuando no. Ajusta cualquier dosis con asesoramiento profesional; aquí tienes información para decidir con criterio.
</p>
  <div class="filters">
    <button class="btn btn-outline btn-sm active" data-brand="todas" title="Todas las marcas premium respaldadas por evidencia">Todas las Marcas Confiables</button>
    <?php foreach ($brands as $b): ?>
      <button class="btn btn-outline btn-sm" data-brand="<?= $b['id'] ?>" title="Productos respaldados por años de investigación">
        <?= htmlspecialchars($b['name']) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="order-bar">
    <label for="order">Ordenar por:</label>
    <select id="order" class="form-select">
      <option value="default">Recomendados</option>
      <option value="price_low">Precio: menor a mayor</option>
      <option value="price_high">Precio: mayor a menor</option>
      <option value="recent">Últimos añadidos</option>
    </select>
  </div>

  <div class="cards">
    <?php foreach ($supplements as $s): ?>
      <div class="card"
        data-brand="<?= $s['idBrand'] ?>"
        data-price="<?= floatval($s['price']) ?>"
        data-created="<?= $s['created_at'] ?>">
        <a href="./supplement.php?id=<?= $s['id'] ?>" class="card-link">
          <div class="card-img">
            <?php $imgs = json_decode($s['images'] ?? '[]', true); ?>
            <img loading="lazy"
                 src="<?= $imgs[0] ?? '../assets/img/no-image.png' ?>"
                 alt="<?= htmlspecialchars($s['name']) ?>">
          </div>
          <div class="card-body">
            <h3><?= htmlspecialchars($s['name']) ?></h3>
            <p><?= substr(htmlspecialchars($s['description']), 0, 70) ?>...</p>
            <span class="brand-tag">
              <?= htmlspecialchars($brandMap[$s['idBrand']] ?? 'Marca oficial') ?>
            </span>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="no-results" style="display:none;">
    No se encontraron suplementos con ese filtro.
  </div>

  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>" class="btn">«</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>" class="btn <?= $i === $page ? 'active' : '' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>" class="btn">»</a>
    <?php endif; ?>
  </div>

  <div class="cta-section">
    <h3 class="cta-title">¿Quieres combinar Beta-Alanina, Glutamina y Creatina?</h3>
    <p class="cta-text">Si ya tienes claras las dosis orientativas que prefieres, diseña tu fórmula personalizada y guárdala para revisarla con tu entrenador o nutricionista.</p>
    <a href="./custom-pack.php" class="btn btn-primary btn-lg">Crear mi fórmula personalizada →</a>
  </div>

</main>

<?php require_once '../components/footer.php'; ?>
</body>
</html>
