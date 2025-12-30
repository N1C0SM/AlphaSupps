<?php
require_once '../models/db.php';
require_once '../models/supplement.php';

$conn = connectToDatabase();
$supplements = getAllSupplements();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Pack Personalizado</title>
<?php require_once '../components/head.php'; ?>

<style>
/* ===================== BASE ===================== */
.custom-pack-page {
  max-width: 960px;
  margin: 0 auto;
  padding: 48px 20px 60px;
  color: var(--text);
}
.hero {
  text-align: center;
  margin-bottom: 42px;
}
.hero h1 {
  color: #f9f5e7;
  font-size: 2.4rem;
  letter-spacing: -0.02em;
}
.hero p {
  color: var(--text-light);
  max-width: 640px;
  margin: 14px auto 0;
  font-size: 1.05rem;
}
.pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(240,199,94,.16), rgba(99,74,16,.32));
  color: #f4d483;
  font-weight: 700;
  font-size: 0.95rem;
  margin-bottom: 14px;
}

/* ===================== FORM ===================== */
.pack-form-container {
  background: radial-gradient(circle at 20% 20%, rgba(240,199,94,.06), transparent 35%),
              radial-gradient(circle at 80% 0%, rgba(146,112,39,.08), transparent 32%),
              var(--bg-panel);
  padding: 32px;
  border-radius: 20px;
  border: 1px solid rgba(240,199,94,.25);
  box-shadow: 0 16px 40px rgba(0,0,0,.35);
}
.form-section {
  margin-bottom: 35px;
}
.form-section h3 {
  border-bottom: 2px solid var(--accent);
  padding-bottom: 8px;
}
.form-label {
  display: block;
  margin: 10px 0 6px;
  font-weight: 600;
}
.form-input,
.form-textarea {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  background: var(--bg-light);
  border: var(--border);
  color: var(--text);
}
.form-textarea {
  resize: vertical;
  min-height: 90px;
}
.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  border-radius: 14px;
  background: rgba(240,199,94,.12);
  color: #f4d483;
  border: 1px solid rgba(240,199,94,.35);
  cursor: pointer;
  font-weight: 700;
  transition: transform .15s ease, box-shadow .15s ease;
}
.ghost-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0,0,0,.28);
}

/* ===================== PRODUCTS ===================== */
.products-list {
  display: grid;
  grid-template-columns: repeat(auto-fill,minmax(200px,1fr));
  gap: 14px;
}
.product-item {
  background: var(--bg-light);
  padding: 16px;
  border-radius: 14px;
  border: var(--border);
  cursor: pointer;
  transition: .2s;
  text-align: center;
}
.product-item:hover {
  border-color: var(--accent);
  transform: translateY(-3px);
}
.product-item.selected {
  background: rgba(240,199,94,.12);
  border-color: var(--accent);
}

/* ===================== SELECTED ===================== */
.selected-products {
  margin-top: 20px;
  padding: 16px;
  border-radius: 14px;
  background: rgba(240,199,94,.06);
  border: 1px solid rgba(240,199,94,.3);
}
.auto-benefits {
  margin-top: 16px;
  padding: 16px;
  border-radius: 12px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.08);
}
.auto-benefits h4 {
  margin: 0 0 10px;
  color: var(--accent);
}
.auto-benefits ul {
  margin: 0 0 8px;
  padding-left: 18px;
  color: var(--text);
  display: grid;
  gap: 6px;
}
.auto-benefits small {
  color: var(--text-muted);
}
.pack-totals {
  margin-top: 16px;
  padding: 14px;
  border-radius: 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  display: grid;
  gap: 8px;
}
.pack-total-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-weight: 600;
  color: var(--text);
}
.pack-total-row.discount {
  color: var(--text-muted);
}
.pack-total-row.total {
  color: var(--accent);
  font-size: 1.05rem;
}
.pack-discount-hint {
  color: var(--text-muted);
  font-size: 0.82rem;
}
.selected-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.qty-controls {
  display: flex;
  gap: 8px;
  align-items: center;
}
.qty-controls button {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: none;
  background: rgba(255,255,255,.12);
  color: white;
  cursor: pointer;
  font-weight: 700;
  transition: transform .1s ease, background .15s ease;
}
.qty-controls button:active {
  transform: translateY(0);
}
.qty-controls button:hover {
  transform: translateY(-1px);
  background: rgba(240,199,94,.25);
}
.qty-controls span {
  min-width: 60px;
  text-align: right;
}

/* ===================== BUTTON ===================== */
.save-btn {
  width: 100%;
  padding: 16px;
  background: var(--accent);
  color: #000;
  font-weight: 700;
  border-radius: 16px;
  border: none;
  cursor: pointer;
}
.save-btn:disabled {
  opacity: .6;
  cursor: not-allowed;
}
.form-feedback {
  display: none;
  margin-bottom: 14px;
  padding: 12px;
  border-radius: 12px;
  font-weight: 600;
}
.form-feedback.error {
  display: block;
  background: rgba(255, 87, 122, 0.12);
  border: 1px solid rgba(255, 87, 122, 0.45);
  color: #ffb7c8;
}
.form-feedback.success {
  display: block;
  background: rgba(224, 185, 77, 0.12);
  border: 1px solid rgba(224, 185, 77, 0.45);
  color: #f5d98a;
}

/* ===================== MODAL ===================== */
.full-rules-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.9);
  display: none;
  align-items: flex-start;
  padding-top: 80px;
  justify-content: center;
  z-index: 99999;
  backdrop-filter: blur(2px);
}
.full-rules-modal.active {
  display: flex;
}
.full-rules-content {
  background: linear-gradient(145deg, rgba(19,19,21,.92), rgba(10,10,12,.96));
  max-width: 700px;
  width: 100%;
  max-height: 85vh;
  border-radius: 20px;
  display: flex;
  flex-direction: column;
}
.full-rules-header {
  padding: 20px;
  border-bottom: 1px solid rgba(240,199,94,.2);
  text-align: center;
  position: relative;
}
.full-rules-header h2 {
  color: var(--accent);
  margin: 0;
}
.rules-list {
  margin: 0;
  padding-left: 18px;
  display: grid;
  gap: 14px;
  color: rgba(255,255,255,.85);
}
.rules-list li {
  line-height: 1.55;
}
.rules-list strong {
  display: block;
  color: var(--accent);
  font-weight: 700;
}
.close-modal-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  background: none;
  border: none;
  color: white;
  font-size: 1.8rem;
  cursor: pointer;
}
.full-rules-body {
  padding: 24px;
  overflow-y: auto;
  color: rgba(255,255,255,.85);
}
.full-rules-body p {
  margin: 0 0 12px;
  color: rgba(255,255,255,.75);
}
</style>
</head>

<body>
<?php require_once '../components/header.php'; ?>

<main class="custom-pack-page">

<!-- MODAL (colocado al inicio para quedar “en frente”) -->
<div id="rules-modal" class="full-rules-modal">
  <div class="full-rules-content">
    <div class="full-rules-header">
      <h2>Normas del Pack</h2>
      <button class="close-modal-btn" onclick="closePackRulesModal()">×</button>
    </div>
    <div class="full-rules-body">
      <ul class="rules-list">
        <li><strong>Objetivo + plazo.</strong> Escribe un objetivo medible y en cuántas semanas piensas alcanzarlo.</li>
        <li><strong>Nombre técnico.</strong> Usa un nombre claro (ej. “Volumen limpio 8s · 5d/sem”) para que se entienda de un vistazo.</li>
        <li><strong>Dosis y timing.</strong> Indica unidades reales y cuándo se toma cada producto; evita duplicar estimulantes.</li>
        <li><strong>Solo catálogo.</strong> Trabaja con los productos de AlphaSupps para mantener trazabilidad y soporte sobre el stock.</li>
        <li><strong>Coherencia y seguridad.</strong> Evita mezclas redundantes, respeta rangos seguros de cafeína y prioriza básicos (proteína, creatina, micronutrientes).</li>
        <li><strong>Contexto del usuario.</strong> Añade peso, nivel y restricciones (intolerancias o no estimulantes) para que otros sepan si el pack les aplica.</li>
      </ul>
    </div>
  </div>
</div>

<section class="hero">
  <div class="pill-badge">📦 Protocolos pro · Sin postureo</div>
  <h1>Crea tu Pack Personalizado</h1>
  <p>Selecciona suplementos y define la cantidad exacta de cada uno. Mostramos beneficios sugeridos según la combinación; revísalos y ajústalos antes de guardar.</p>
  <button type="button" class="ghost-btn" onclick="openPackRulesModal()">📖 Ver normas senior</button>
</section>

<div class="pack-form-container">
<form id="pack-form" action="../controllers/pack.php" method="POST">

<input type="hidden" name="action" value="create_private_pack">
<input type="hidden" name="features" id="selected-items-input">
<input type="hidden" name="total_price" id="total-price-input">
<input type="hidden" name="benefits" id="benefits-input">
<div id="form-feedback" class="form-feedback" role="alert"></div>

<!-- INFO -->
<div class="form-section">
  <h3>Información del Pack</h3>

  <label class="form-label">Nombre del Pack</label>
  <input type="text" name="name" class="form-input" required>

  <label class="form-label">Descripción</label>
  <textarea name="description" class="form-textarea" required></textarea>
</div>

<!-- PRODUCTS -->
<div class="form-section">
  <h3>Productos</h3>

  <div class="products-list">
    <?php foreach ($supplements as $p): ?>
      <?php
        $benefitsList = [];
        if (!empty($p['benefits'])) {
          $decoded = is_array($p['benefits']) ? $p['benefits'] : json_decode($p['benefits'], true);
          if (is_array($decoded)) {
            $benefitsList = array_values(array_filter($decoded, fn($b) => is_string($b) && trim($b) !== ''));
          }
        }
        $benefitsAttr = htmlspecialchars(json_encode($benefitsList), ENT_QUOTES, 'UTF-8');
      ?>
      <div class="product-item"
        data-id="<?= $p['id'] ?>"
        data-name="<?= htmlspecialchars($p['name']) ?>"
        data-price="<?= $p['price'] ?>"
        data-benefits="<?= $benefitsAttr ?>">
        <strong><?= htmlspecialchars($p['name']) ?></strong><br>
        €<?= number_format($p['price'],2) ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div id="selected-products" class="selected-products" style="display:none;"></div>
  <div id="auto-benefits" class="auto-benefits" style="display:none;">
    <h4>Beneficios sugeridos</h4>
    <ul id="auto-benefits-list"></ul>
    <small>Se sugieren automáticamente según los productos elegidos; revísalos antes de publicar.</small>
  </div>
  <div id="pack-totals" class="pack-totals" style="display:none;">
    <div class="pack-total-row">
      <span>Subtotal</span>
      <span id="pack-subtotal">0.00 €</span>
    </div>
    <div class="pack-total-row discount">
      <span id="pack-discount-label">Descuento</span>
      <span id="pack-discount">0.00 €</span>
    </div>
    <div class="pack-total-row total">
      <span>Total</span>
      <span id="pack-total">0.00 €</span>
    </div>
    <small class="pack-discount-hint">2 productos: 5% · 3 productos: 7% · 4+ productos: 10%</small>
  </div>
</div>

<button type="submit" class="save-btn" id="save-btn" disabled>
💾 Crear Pack
</button>


</form>
</div>

</main>

<script>
/* ===================== JS (FUNCIONA) ===================== */
let selectedProducts = [];
const benefitsBox = document.getElementById('auto-benefits');
const benefitsList = document.getElementById('auto-benefits-list');
const benefitsInput = document.getElementById('benefits-input');
const totalsBox = document.getElementById('pack-totals');
const subtotalEl = document.getElementById('pack-subtotal');
const discountEl = document.getElementById('pack-discount');
const discountLabelEl = document.getElementById('pack-discount-label');
const totalEl = document.getElementById('pack-total');
const selectedProductsBox = document.getElementById('selected-products');

document.querySelectorAll('.product-item').forEach(item => {
  item.addEventListener('click', () => toggleProduct(item));
});

function toggleProduct(item) {
  const id = String(item.dataset.id);
  const name = item.dataset.name;
  const price = parseFloat(item.dataset.price);
  const benefits = parseBenefits(item.dataset.benefits);

  const index = selectedProducts.findIndex(p => p.id === id);

  if (index > -1) {
    selectedProducts.splice(index, 1);
    item.classList.remove('selected');
  } else {
    selectedProducts.push({ id, name, price, qty: 1, benefits });
    item.classList.add('selected');
  }

  renderSelected();
}

function changeQty(id, delta) {
  const product = selectedProducts.find(p => p.id === String(id));
  if (!product) return;

  product.qty = Math.max(1, product.qty + delta);
  renderSelected();
  return false;
}
// Mantener accesible por si algún botón inline lo invoca
window.changeQty = changeQty;

// Delegamos clicks en +/- dentro del bloque seleccionado sin afectar a otros botones del sitio
if (selectedProductsBox) {
  selectedProductsBox.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    e.preventDefault();
    const id = btn.dataset.id;
    const delta = btn.dataset.action === 'inc' ? 1 : -1;
    changeQty(id, delta);
  });
}

function renderSelected() {
  const box = document.getElementById('selected-products');
  const btn = document.getElementById('save-btn');

  if (selectedProducts.length === 0) {
    box.style.display = 'none';
    if (benefitsBox) benefitsBox.style.display = 'none';
    if (totalsBox) totalsBox.style.display = 'none';
    btn.disabled = true;
    btn.textContent = '💾 Crear Pack';
    return;
  }

  box.style.display = 'block';
  box.innerHTML = selectedProducts.map(p => `
    <div class="selected-item">
      <span>${p.name}</span>
      <div class="qty-controls">
        <button type="button" data-action="dec" data-id="${p.id}">−</button>
        <strong>${p.qty}</strong>
        <button type="button" data-action="inc" data-id="${p.id}">+</button>
        <span>€${(p.price * p.qty).toFixed(2)}</span>
      </div>
    </div>
  `).join('');

  const subtotal = selectedProducts.reduce((sum,p)=>sum+p.price*p.qty,0);
  const itemCount = selectedProducts.length;
  let discountRate = 0;
  if (itemCount >= 4) {
    discountRate = 0.10;
  } else if (itemCount === 3) {
    discountRate = 0.07;
  } else if (itemCount === 2) {
    discountRate = 0.05;
  }
  const discount = subtotal * discountRate;
  const total = subtotal - discount;

  document.getElementById('selected-items-input').value =
    JSON.stringify(selectedProducts);

  document.getElementById('total-price-input').value =
    total.toFixed(2);

  const autoBenefits = buildBenefits(selectedProducts);
  if (benefitsBox && benefitsList) {
    benefitsList.innerHTML = autoBenefits.map(b => `<li>${b}</li>`).join('');
    benefitsBox.style.display = autoBenefits.length ? 'block' : 'none';
  }
  if (benefitsInput) {
    benefitsInput.value = JSON.stringify(autoBenefits);
  }

  btn.disabled = false;
  btn.textContent = `💾 Crear Pack (€${total.toFixed(2)})`;

  if (totalsBox) {
    totalsBox.style.display = 'grid';
  }
  if (subtotalEl) subtotalEl.textContent = `${subtotal.toFixed(2)} €`;
  if (discountEl) discountEl.textContent = `-${discount.toFixed(2)} €`;
  if (discountLabelEl) {
    const pct = Math.round(discountRate * 100);
    discountLabelEl.textContent = pct > 0 ? `Descuento (${pct}%)` : 'Descuento';
  }
  if (totalEl) totalEl.textContent = `${total.toFixed(2)} €`;
}

const form = document.getElementById('pack-form');
const feedbackBox = document.getElementById('form-feedback');

function setFeedback(type, text) {
  feedbackBox.classList.remove('error', 'success');
  if (!text) {
    feedbackBox.style.display = 'none';
    feedbackBox.textContent = '';
    return;
  }
  feedbackBox.classList.add(type);
  feedbackBox.style.display = 'block';
  feedbackBox.textContent = text;
}

form.addEventListener('submit', (e) => {
  const name = (form.querySelector('input[name=\"name\"]')?.value || '').trim();
  const description = (form.querySelector('textarea[name=\"description\"]')?.value || '').trim();

  if (selectedProducts.length === 0) {
    e.preventDefault();
    setFeedback('error', 'Selecciona al menos un producto antes de guardar tu pack.');
    window.scrollTo({ top: form.offsetTop - 40, behavior: 'smooth' });
    return;
  }

  if (!name || !description) {
    e.preventDefault();
    setFeedback('error', 'Completa nombre y descripción para entender tu objetivo.');
    return;
  }

  setFeedback('success', 'Guardando tu pack personalizado…');
});

function parseBenefits(raw = '') {
  if (!raw) return [];
  try {
    const parsed = JSON.parse(raw);
    if (Array.isArray(parsed)) {
      return parsed.filter(b => typeof b === 'string' && b.trim() !== '');
    }
  } catch (e) {
    return [];
  }
  return [];
}

function deriveBenefitFromName(name = '') {
  const n = (name || '').toLowerCase();
  if (n.includes('prote')) return 'Construcción muscular';
  if (n.includes('whey')) return 'Apoyo a la recuperación post-entreno';
  if (n.includes('creat')) return 'Mejora de fuerza y potencia';
  if (n.includes('bcaa') || n.includes('amino')) return 'Menos fatiga y mejor recuperación';
  if (n.includes('pre') || n.includes('cafe') || n.includes('cafei')) return 'Energía y enfoque pre-entreno';
  if (n.includes('beta')) return 'Rendimiento anaeróbico sostenido';
  if (n.includes('omega') || n.includes('epa') || n.includes('dha')) return 'Salud articular y cardiovascular';
  if (n.includes('vit') || n.includes('multi')) return 'Soporte inmunitario y micronutrientes';
  if (n.includes('glut')) return 'Recuperación y salud intestinal';
  if (n.includes('zma') || n.includes('magnes')) return 'Sueño y recuperación muscular';
  if (n.includes('carb') || n.includes('gainer')) return 'Energía sostenida y soporte calórico';
  return 'Beneficio complementario para tu objetivo';
}

function buildBenefits(items = []) {
  const list = [];
  items.forEach(item => {
    const benefits = Array.isArray(item.benefits) && item.benefits.length
      ? item.benefits
      : [deriveBenefitFromName(item.name)];
    benefits.forEach(b => list.push(b));
  });
  return Array.from(new Set(list.map(b => b.trim()).filter(Boolean)));
}

/* MODAL */
function openPackRulesModal() {
  document.getElementById('rules-modal').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closePackRulesModal() {
  document.getElementById('rules-modal').classList.remove('active');
  document.body.style.overflow = '';
}
</script>

</body>
</html>
