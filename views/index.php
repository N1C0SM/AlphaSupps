<?php
require_once '../controllers/index.php';
require_once '../models/db.php';
require_once '../models/pack.php';
require_once '../models/collection.php';

$conn = connectToDatabase();

$allPacks = getPublicPacks($conn);

$highlightedPacks = array_values(array_filter($allPacks, fn($p) => (int)($p['highlighted'] ?? 0) === 1));
$packs = $highlightedPacks;
?>

<!DOCTYPE html>
<html lang="es">

<?php require_once '../components/head.php'; ?>
<?php require_once '../components/cookies.php'; ?>

<body>
<?php require_once '../components/header.php'; ?>

<main class="home">

  <section class="hero">
    <h1>Suplementos que Evolucionan Contigo.<br>No Fórmulas Estáticas.</h1>
    <p class="section-subtitle">
      AlphaSupps no calcula dosis ni promete resultados. Te damos fichas claras, rangos orientativos y contexto
      para que ajustes con tu entrenador o nutricionista. Transparencia primero.
    </p>
    <?php if (isset($_SESSION['user']) && $_SESSION['user']): ?>
      <a href="./custom-pack.php" class="btn btn-primary btn-lg">Crear Mi Pack Personalizado →</a>
    <?php else: ?>
      <a href="./register.php" class="btn btn-primary btn-lg">Crear mi cuenta para Pack Personalizado →</a>
    <?php endif; ?>
  </section>

  <section class="why-us">
    <h2>Por Qué AlphaSupps Es Diferente</h2>
    <div class="why-grid">

      <div class="why-item">
        <div class="icon">🧪</div>
        <h3>Referencias claras, sin algoritmos mágicos</h3>
        <p>Si pesas 75kg y entrenas 4x/semana, verás rangos orientativos y notas de uso basadas en literatura pública. Las decisiones son tuyas y de tu profesional.</p>
      </div>

      <div class="why-item">
        <div class="icon">🏋️</div>
        <h3>Ajusta tu pack cuando cambie tu rutina</h3>
        <p>Edita productos y cantidades en cualquier momento y guarda versiones. No hay ajustes automáticos ni promesas de resultados, solo control manual.</p>
      </div>

      <div class="why-item">
        <div class="icon">📦</div>
        <h3>Reposición programada que controlas</h3>
        <p>Configura lo que quieres recibir y cada cuánto. Te avisamos antes de enviar para que ajustes o pauses sin sorpresas.</p>
      </div>

      <div class="why-item">
        <div class="icon">⭐</div>
        <h3>Marcas auditables y trazabilidad</h3>
        <p>Trabajamos con proveedores con certificaciones de calidad y documentación de lote. Si necesitas más detalles, los compartimos antes de que compres.</p>
      </div>

    </div>
  </section>

  <section class="brands">
    <h2>Nuestras marcas</h2>
    <div class="brand-logos">
      <img loading="lazy" decoding="async" width="433" height="55" src="../images/brands/myprotein.svg" alt="MyProtein">
      <img loading="lazy" decoding="async" width="1261" height="388" src="../images/brands/prozis.png" alt="Prozis">
      <img loading="lazy" decoding="async" width="170" height="36" src="../images/brands/optimum.svg" alt="Optimum Nutrition">
      <img loading="lazy" decoding="async" width="72" height="72" src="../images/brands/hsn.svg" alt="HSN">
    </div>
  </section>

  <section class="top-sales" id="packs">
    <h2>Packs pensados para objetivos reales</h2>

    <p class="section-subtitle">
      Cada pack incluye dosis orientativas y notas de uso para distintos objetivos. Revísalo y ajústalo a tus necesidades con criterio profesional.
    </p>

    <div class="cards">

      <?php foreach ($packs as $pack): ?>

        <a href="../views/pack.php?id=<?= $pack['id'] ?>" class="card top-card">

          <img class="top-img" loading="lazy" decoding="async" width="1200" height="800" src="<?= $pack['images'][0] ?>" alt="<?= htmlspecialchars($pack['name']) ?>">

          <?php if ($pack['label']): ?>
            <span class="badge-label"><?= htmlspecialchars($pack['label']) ?></span>
          <?php endif; ?>

          <h3><?= htmlspecialchars($pack['name']) ?></h3>

          <span class="price-tag"><?= htmlspecialchars($pack['price']) ?>€</span>

        </a>

      <?php endforeach; ?>

    </div>

    <p style="text-align:center; margin-top:2rem;">
      <a href="../views/packs.php" class="btn btn-primary btn-lg">Ver todos los packs →</a>
    </p>

  </section>

  <section class="why-us" id="alphabox">
    <h2>AlphaBox: reposición programada sin promesas exageradas</h2>
    <div class="why-grid">

      <div class="why-item">
        <div class="icon">📦</div>
        <h3>Planes de reposición que decides tú</h3>
        <p>Indica cantidades y frecuencia y te recordamos antes de enviar. Puedes pausar o ajustar cada ciclo sin automatismos opacos.</p>
      </div>

      <div class="why-item">
        <div class="icon">💰</div>
        <h3>Descuento recurrente para suscriptores</h3>
        <p>Aplica un ahorro mensual mientras mantengas la suscripción. Si el precio cambia, te avisamos antes de renovar.</p>
      </div>

      <div class="why-item">
        <div class="icon">⚡</div>
        <h3>Envíos habituales según stock</h3>
        <p>Trabajamos con entregas 24/48h según stock y transportista. Si algo se retrasa, lo sabrás antes de confirmar.</p>
      </div>

      <div class="why-item">
        <div class="icon">⏳</div>
        <h3>Plazas limitadas por logística</h3>
        <p>Abrimos plazas de forma gradual para asegurar stock y atención. Si el cupo está lleno, podrás unirte a la lista de aviso.</p>
      </div>

    </div>

    <div style="text-align:center; margin-top:1.5rem;">
      <p style="margin-bottom:2em;color:var(--text-light);">
        ¿Prefieres controlar cada compra? Usa las suscripciones normales. ¿Quieres recordatorios y reposición programada? Elige AlphaBox (precio de suscriptor y avisos previos).
      </p>
      <a href="./alphabox.php" class="btn btn-primary">Ver AlphaBox y reservar plaza →</a>
    </div>
  </section>

  <?php if (!empty($topSales)): ?>
  <section class="top-sales">
    <h2>Más elegidos</h2>

    <p class="section-subtitle">
      Estos son los suplementos que más eligen personas como tú.
    </p>

    <div class="cards">
      <?php foreach ($topSales as $s):
        $imgs = $s['images'] ? json_decode($s['images'], true) : [];
        $img = $imgs[0] ?? '../assets/img/no-image.png';
      ?>
      <a href="./supplement/<?= $s['id'] ?>" class="card top-card">
        <img class="top-img" loading="lazy" decoding="async" width="1200" height="800" src="<?= $img ?>" alt="<?= htmlspecialchars($s['name']) ?>">
        <h3><?= htmlspecialchars($s['name']) ?></h3>
        <span class="price-tag">€<?= htmlspecialchars($s['price']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>

  </section>
  <?php endif; ?>

  <section id="colecciones" class="collections-wrapper">

    <?php foreach ($collections as $collection): ?>
    <section class="collection-block" id="collection-<?= $collection['id'] ?>">
      <h2 class="collection-title">
        <a href="../views/collection.php?id=<?= $collection['id'] ?>">
          <?= htmlspecialchars($collection['name']) ?>: pensado para lo que quieres conseguir
        </a>
      </h2>

      <?php
        $supps = $collection['supplements'] ?? [];
        if (empty($supps)) {
          $supps = getSupplementsByCollectionId((int)$collection['id'], $conn);
        }
      ?>
      <?php if (!empty($supps)): ?>
      <div class="cards">
        <?php foreach ($supps as $s):
          $images = $s['images'] ? json_decode($s['images'], true) : [];
          $img = $images[0] ?? '../assets/img/no-image.png';
        ?>
        <a href="../views/supplement.php?id=<?= $s['id'] ?>" class="card">
          <div class="thumb">
            <img loading="lazy" decoding="async" width="400" height="400" src="<?= $img ?>" alt="<?= htmlspecialchars($s['name']) ?>">
          </div>
          <h3><?= htmlspecialchars($s['name']) ?></h3>
          <button class="cta-btn small">Ver producto</button>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </section>
    <?php endforeach; ?>

  </section>
</main>

<?php require_once '../components/footer.php'; ?>
</body>
</html>
