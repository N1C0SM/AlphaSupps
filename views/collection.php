<?php require_once '../controllers/collection.php' ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../components/head.php' ?>
  <?php require_once '../components/cookies.php';?>
<body>
  <?php require_once '../components/header.php'; ?>
  <main class="collection-page">
    <section class="collection-header">
    <h2><?= $collection['name'] ?></h2>
    <p><?= $collection['description'] ?></p>
    </section>
    <section class="filters">
      <button data-brand="todas" class="active">Todas</button>
      <?php foreach ($brands as $brand): ?>
        <button data-brand="<?= htmlspecialchars($brand['id']) ?>">
          <?= htmlspecialchars($brand['name']) ?>
        </button>
      <?php endforeach; ?>
    </section>
    <section class="cards">
      <?php foreach ($suplements as $suplement): ?>
        <a href="./supplement.php?id=<?= htmlspecialchars($suplement['id']) ?>"
          class="card"
          data-brand="<?= htmlspecialchars($suplement['idBrand']) ?>">
          <div class="card-img">
            <?php $images = json_decode($suplement['images'] ?? '[]', true); ?>
            <?php if (!empty($images)): ?>
              <img loading="lazy" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($suplement['name']) ?>">
            <?php else: ?>
              <img loading="lazy" src="../assets/img/no-image.png" alt="Sin imagen">
            <?php endif; ?>
          </div>
          <div class="card-body">
            <h3><?= htmlspecialchars($suplement['name']) ?></h3>
            <p><?= htmlspecialchars(substr($suplement['description'] ?? '', 0, 80)) ?>...</p>
            <span class="brand-tag">
                <?= htmlspecialchars($brandMap[$suplement['idBrand']] ?? 'Marca desconocida') ?>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </section>
    <section class="no-results" style="display:none;">
      <p>No se encontraron suplementos para esta marca.</p>
    </section>
  </main>
<?php require_once '../components/footer.php'; ?>
</body>
</html>