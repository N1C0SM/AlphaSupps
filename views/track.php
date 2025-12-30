<!doctype html>
<html lang="es">

<?php require_once '../components/head.php'; ?>

<body>
<?php require_once '../components/header.php'; ?>

<main class="track-wrapper">
  <section class="track-card">
    <div class="track-header">
      <div>
        <p class="eyebrow">Seguimiento de pedido</p>
        <h1>Consulta el estado de tu envío</h1>
        <p class="subtitle">Introduce el ID de pedido y el email con el que compraste para ver el estado y el tracking.</p>
      </div>
      <div class="badge">24/7</div>
    </div>

    <form id="track-form" class="track-form" autocomplete="off" spellcheck="false">
      <div class="field">
        <label for="order_id">ID de pedido</label>
        <input type="number" id="order_id" name="order_id" min="1" placeholder="Ej: 109" required>
      </div>
      <div class="field">
        <label for="email">Email del pedido</label>
        <input type="email" id="email" name="email" placeholder="tu@email.com" required>
      </div>
      <button type="submit" class="btn-submit">Ver estado</button>
      <p id="track-error" class="error hidden"></p>
    </form>

    <div id="track-result" class="track-result hidden">
      <div class="status-row">
        <div>
          <p class="label">Estado</p>
          <p id="status-text" class="status-text">-</p>
        </div>
        <span id="status-badge" class="chip">--</span>
      </div>

      <div class="data-grid">
        <div class="data-item">
          <p class="label">Pedido</p>
          <p id="order-label">-</p>
        </div>
        <div class="data-item">
          <p class="label">Fecha</p>
          <p id="order-date">-</p>
        </div>
        <div class="data-item">
          <p class="label">Importe</p>
          <p id="order-amount">-</p>
        </div>
        <div class="data-item">
          <p class="label">Tracking</p>
          <p id="tracking-code">-</p>
        </div>
      </div>

      <div class="actions">
        <a id="tracking-link" class="btn-link hidden" target="_blank" rel="noopener noreferrer">Abrir tracking</a>
        <span id="no-tracking" class="muted">Aún no hay enlace de tracking, seguimos preparando tu pedido.</span>
      </div>
    </div>
  </section>
</main>

<?php require_once '../components/footer.php'; ?>

<style>
  .track-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 16px 80px;
    background: radial-gradient(circle at 10% 20%, rgba(224,185,77,0.12), transparent 30%),
                radial-gradient(circle at 80% 0%, rgba(224,185,77,0.12), transparent 28%),
                var(--bg-dark);
  }
  .track-card {
    width: 100%;
    max-width: 880px;
    background: var(--bg-panel);
    border: 1px solid var(--color-border);
    border-radius: 18px;
    box-shadow: var(--shadow);
    padding: 32px;
  }
  .track-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
  }
  .eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    font-size: 12px;
    margin: 0 0 6px;
  }
  .track-header h1 {
    margin: 0 0 6px;
    font-size: 28px;
    color: #fff;
  }
  .subtitle {
    margin: 0;
    color: var(--text-light);
    font-size: 15px;
    max-width: 580px;
  }
  .badge {
    background: var(--gradient-primary);
    color: #000;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 700;
    box-shadow: var(--shadow-gold);
  }
  .track-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 18px;
  }
  .field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .field label {
    color: var(--text-muted);
    font-size: 14px;
  }
  .field input {
    background: var(--bg-light);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 12px;
    color: #fff;
    font-size: 15px;
  }
  .btn-submit {
    align-self: end;
    background: var(--gradient-primary);
    color: #000;
    border: none;
    border-radius: 12px;
    padding: 12px 18px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: var(--shadow-gold);
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
  }
  .btn-submit:hover { transform: translateY(-1px); box-shadow: var(--shadow-gold-hover); }
  .btn-submit:active { transform: translateY(0); }
  .error {
    color: #f87171;
    margin: 0;
    font-size: 14px;
  }
  .hidden { display: none; }
  .track-result {
    margin-top: 10px;
    padding: 16px;
    border-radius: 14px;
    border: 1px solid var(--color-border);
    background: linear-gradient(180deg, rgba(224,185,77,0.06) 0%, rgba(224,185,77,0.02) 100%);
  }
  .status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
  }
  .label {
    color: var(--text-muted);
    font-size: 13px;
    margin: 0 0 4px;
  }
  .status-text {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #fff;
  }
  .chip {
    background: rgba(224,185,77,0.12);
    color: var(--accent);
    border: 1px solid rgba(224,185,77,0.4);
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 700;
  }
  .data-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
  }
  .data-item {
    padding: 12px;
    border-radius: 10px;
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--color-border);
  }
  .data-item p {
    margin: 0;
    color: #fff;
  }
  .actions {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }
  .btn-link {
    display: inline-block;
    background: var(--accent);
    color: #000;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: var(--shadow-gold);
  }
  .muted {
    color: var(--text-muted);
    font-size: 14px;
  }
  @media (max-width: 640px) {
    .track-card { padding: 24px; }
    .track-header { flex-direction: column; align-items: flex-start; }
    .btn-submit { width: 100%; }
  }
</style>

<script>
  const form = document.getElementById('track-form');
  const resultBox = document.getElementById('track-result');
  const errorBox = document.getElementById('track-error');
  const statusText = document.getElementById('status-text');
  const statusBadge = document.getElementById('status-badge');
  const orderLabel = document.getElementById('order-label');
  const orderDate = document.getElementById('order-date');
  const orderAmount = document.getElementById('order-amount');
  const trackingCode = document.getElementById('tracking-code');
  const trackingLink = document.getElementById('tracking-link');
  const noTracking = document.getElementById('no-tracking');
  const orderInput = document.getElementById('order_id');
  const emailInput = document.getElementById('email');

  function mapStatus(status) {
    const normalized = (status || '').toLowerCase();
    if (normalized.includes('delivered')) return { text: 'Entregado', chip: 'Entregado' };
    if (normalized.includes('shipped') || normalized.includes('out_for_delivery')) return { text: 'En tránsito', chip: 'En tránsito' };
    if (normalized.includes('submitted') || normalized.includes('pending')) return { text: 'Preparando envío', chip: 'Preparando' };
    return { text: status || 'En proceso', chip: status || 'Proceso' };
  }

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorBox.classList.add('hidden');
    resultBox.classList.add('hidden');

    const payload = {
      order_id: parseInt(document.getElementById('order_id').value, 10),
      email: document.getElementById('email').value.trim()
    };

    if (!payload.order_id || !payload.email) {
      errorBox.textContent = 'Rellena el ID de pedido y tu email.';
      errorBox.classList.remove('hidden');
      return;
    }

    const btn = e.submitter || form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;

    try {
      const res = await fetch('../api/track-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (!data.success) {
        throw new Error(data.error || 'No se pudo obtener el pedido.');
      }

      const order = data.order;
      const status = mapStatus(order.fulfillment_status);
      statusText.textContent = status.text;
      statusBadge.textContent = status.chip;

      orderLabel.textContent = `#${order.id} · ${order.product || 'Pedido'}`;
      orderDate.textContent = formatDate(order.created_at);
      orderAmount.textContent = order.price ? `${Number(order.price).toFixed(2)} €` : '-';
      trackingCode.textContent = order.tracking_code || 'Pendiente';

      if (order.tracking_url) {
        trackingLink.href = order.tracking_url;
        trackingLink.classList.remove('hidden');
        noTracking.classList.add('hidden');
      } else {
        trackingLink.classList.add('hidden');
        noTracking.classList.remove('hidden');
      }

      resultBox.classList.remove('hidden');
    } catch (err) {
      errorBox.textContent = err.message || 'No pudimos obtener el estado de tu pedido.';
      errorBox.classList.remove('hidden');
    } finally {
      if (btn) btn.disabled = false;
    }
  });

  // Prefill desde query params
  const params = new URLSearchParams(window.location.search);
  const paramOrder = params.get('order_id');
  const paramEmail = params.get('email');
  if (paramOrder) {
    orderInput.value = paramOrder;
  }
  if (paramEmail) {
    emailInput.value = paramEmail;
  }
  if (paramOrder && paramEmail) {
    form.requestSubmit();
  }
</script>

</body>
</html>
