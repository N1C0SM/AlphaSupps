<?php require_once '../controllers/supplement.php'; ?>
<!DOCTYPE html>
<html lang="es">
<?php require_once '../components/head.php'; ?>
<body>
<?php require_once '../components/header.php'; ?>

<?php
$imgs = $supplement['images'] ?? [];
$galleryImages = array_values(array_filter($imgs));
if (!$galleryImages) $galleryImages = ['../assets/img/no-image.png'];

$img = $galleryImages[0];
$hasMultipleImages = count($galleryImages) > 1;

$features = $supplement['features'] ?? [];
$benefits = $supplement['benefits'] ?? [];
$featureItems = array_map(function ($f) {
    if (is_array($f)) {
        if (!empty($f['name'])) {
            $qty = isset($f['qty']) ? max(1, (int) $f['qty']) : null;
            return trim(($qty ? $qty . ' x ' : '') . $f['name']);
        }
        $flat = array_filter($f, fn ($v) => !is_array($v) && $v !== '' && $v !== null);
        return implode(' ', $flat);
    }
    return $f;
}, is_array($features) ? $features : []);

$benefitItems = [];
if (is_array($benefits)) {
    foreach ($benefits as $b) {
        if (is_array($b)) {
            foreach ($b as $sub) {
                if (!is_array($sub)) {
                    $benefitItems[] = $sub;
                }
            }
        } else {
            $benefitItems[] = $b;
        }
    }
} else {
    $benefitItems[] = $benefits;
}

$price  = floatval($supplement['price'] ?? 0);
$rating = floatval($supplement['rating'] ?? 0);
$userRating = round($rating);
$canRate = true;
?>

<?php require_once '../components/product-inline-styles.php'; ?>

<main>
<section class="product-detail">

<!-- GALERÍA -->
<div class="product-gallery">
  <div class="gallery-main">
    <?php if($hasMultipleImages): ?><button class="gallery-arrow prev" onclick="prevImage()">‹</button><?php endif; ?>
    <img src="<?= $img ?>" id="main-product-image" width="1200" height="800" decoding="async" fetchpriority="high" loading="eager" alt="<?= htmlspecialchars($supplement['name']) ?>">
    <?php if($hasMultipleImages): ?><button class="gallery-arrow next" onclick="nextImage()">›</button><?php endif; ?>
  </div>

  <?php if($hasMultipleImages): ?>
  <div class="thumbnail-gallery">
    <?php foreach($galleryImages as $i=>$image): ?>
      <img src="<?= $image ?>" class="thumbnail <?= $i===0?'active':'' ?>" width="120" height="120" loading="lazy" decoding="async" onclick="goToImage(<?= $i ?>)">
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- INFO -->
<div class="product-info">
  <h1><?= htmlspecialchars($supplement['name']) ?></h1>

  <!-- RATING -->
  <div class="product-rating" data-supplement-id="<?= $supplement['id'] ?>" data-can-rate="<?= $canRate ? '1' : '0' ?>">
    <?php for($i=1;$i<=5;$i++): ?>
      <button class="rating-star <?= $i <= $userRating?'active':'' ?>" data-value="<?= $i ?>" <?= $canRate ? '' : 'aria-disabled="true"' ?>>★</button>
    <?php endfor; ?>
    <div class="rating-meta">
      <span class="rating-value" id="supp-rating-value"><?= number_format($rating,1) ?></span>/5
      <small id="supp-rating-message"><?= $rating > 0 ? 'Pulsa para valorar' : 'Sé el primero en valorar' ?></small>
    </div>
  </div>

  <!-- PRECIO + SUSCRIPCIÓN -->
  <div class="product-pricing" style="margin:14px 0;">
    <div class="price-line">
      <span class="current-price" id="base-price" data-price="<?= $price ?>"><?= number_format($price,2) ?> €</span>
    </div>
    <small style="color:var(--text-muted);">Envío gratis • Pago seguro</small>
  </div>

  <!-- FEATURES -->
  <?php if($featureItems || $benefitItems): ?>
  <div class="product-features-benefits">
    <?php if($featureItems): ?>
    <div>
      <h4>📦 Qué incluye</h4>
      <ul><?php foreach($featureItems as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <?php if($benefitItems): ?>
    <div>
      <h4>⚡ Beneficios clave</h4>
      <ul><?php foreach($benefitItems as $b): ?><li><?= htmlspecialchars($b) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="subscription-block">
    <label class="subscription-switch">
      <input type="checkbox" id="subscribe-toggle">
      <span>Suscribirse con precio de suscriptor</span>
    </label>
    <div id="subscribe-options" class="subscription-details">
      <label for="subscribe-interval">Frecuencia:</label>
      <select id="subscribe-interval">
        <option value="1">Cada mes</option>
        <option value="2">Cada 2 meses</option>
        <option value="3" selected>Cada 3 meses</option>
      </select>
      <span class="subscription-price">Precio suscripción: <strong id="subscription-price"></strong></span>
    </div>
  </div>

  <!-- CTA -->
  <div class="purchase-buttons">
    <button class="cta-btn" onclick='addToCartWithSubscription({id:"supplement_<?= $supplement["id"] ?>",name:"<?= htmlspecialchars($supplement["name"]) ?>",price:<?= $price ?>,icon:"<?= $img ?>"})'>Añadir al carrito</button>
    <button class="cta-btn secondary" onclick='instantBuyWithSubscription({id:"supplement_<?= $supplement["id"] ?>",name:"<?= htmlspecialchars($supplement["name"]) ?>",price:<?= $price ?>,icon:"<?= $img ?>"})'>Comprar ahora</button>
  </div>

</div>
</section>
</main>

<!-- GALERÍA JS -->
<script>
const galleryImages = <?= json_encode($galleryImages) ?>;
let currentGalleryIndex=0;
function renderGalleryImage(i){
  currentGalleryIndex=i;
  document.getElementById('main-product-image').src=galleryImages[i];
  document.querySelectorAll('.thumbnail').forEach((t,idx)=>t.classList.toggle('active',idx===i));
}
function nextImage(){renderGalleryImage((currentGalleryIndex+1)%galleryImages.length)}
function prevImage(){renderGalleryImage((currentGalleryIndex-1+galleryImages.length)%galleryImages.length)}
function goToImage(i){renderGalleryImage(i)}
</script>

<script>
(() => {
  const ratingBox = document.querySelector('.product-rating');
  if (!ratingBox) return;

  const stars = ratingBox.querySelectorAll('.rating-star');
  const ratingValueEl = document.getElementById('supp-rating-value');
  const messageEl = document.getElementById('supp-rating-message');
  const supplementId = parseInt(ratingBox.dataset.supplementId, 10);
  let currentRating = parseFloat(ratingValueEl?.textContent || '0') || 0;
  const canRate = ratingBox.dataset.canRate === '1';

  const notify = (msg, type = 'success') => {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else {
      console[type === 'error' ? 'error' : 'log'](msg);
    }
  };

  function paintStars(val) {
    stars.forEach(star => {
      star.classList.toggle('active', parseInt(star.dataset.value, 10) <= val);
    });
  }

  async function sendVote(val) {
    if (!canRate) {
      notify('No puedes valorar este producto por ahora.', 'error');
      return;
    }
    if (messageEl) messageEl.textContent = 'Guardando voto…';
    try {
      const res = await fetch('../api/rate-product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          action: 'rate',
          product_id: supplementId,
          rating: val,
          tipo: 'supplement'
        })
      });

      const data = await res.json().catch(async () => ({
        success: false,
        message: await res.text()
      }));

      if (!data.success) {
        throw new Error(data.message || 'No pudimos guardar tu voto.');
      }

      currentRating = val;
      if (ratingValueEl) {
        const newAvg = data.newRating ?? val;
        ratingValueEl.textContent = (parseFloat(newAvg) || val).toFixed(1);
      }
      if (messageEl) messageEl.textContent = data.message || 'Gracias por valorar.';
      notify('Gracias por valorar.', 'success');
    } catch (err) {
      notify(err?.message || 'Error al votar.', 'error');
      if (messageEl) messageEl.textContent = err?.message || 'Error al votar.';
    }

    paintStars(currentRating);
  }

  paintStars(currentRating);

  stars.forEach(star => {
    const val = parseInt(star.dataset.value, 10);
    star.addEventListener('click', () => sendVote(val));
    star.addEventListener('mouseenter', () => paintStars(val));
    star.addEventListener('mouseleave', () => paintStars(currentRating));
  });
})();

// Suscripción: actualizar precio mostrado
(function(){
  const basePriceEl = document.getElementById('base-price');
  const subPriceEl = document.getElementById('subscription-price');
  const toggle = document.getElementById('subscribe-toggle');
  const details = document.getElementById('subscribe-options');
  if (!basePriceEl || !subPriceEl || !toggle || !details) return;

  const base = parseFloat(basePriceEl.dataset.price || '0') || 0;

  function refresh() {
    const sub = base * 0.9;
    subPriceEl.textContent = sub.toFixed(2) + ' €';
    if (toggle.checked) {
      details.classList.add('active');
    } else {
      details.classList.remove('active');
    }
  }

  toggle.addEventListener('change', refresh);
  document.getElementById('subscribe-interval')?.addEventListener('change', refresh);
  refresh();
})();
</script>

<?php require_once '../components/footer.php'; ?>
</body>
</html>
