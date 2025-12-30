<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user']['id'] ?? null;
?>
<?php require_once '../components/head.php'; ?>
<?php require_once '../components/header.php'; ?>

<main class="alphabox-page">

<section class="hero">
  <h1>Planifica tu AlphaBox sin promesas mágicas</h1>
  <p>Indica consumo aproximado y frecuencia deseada. Por ahora los ajustes de cantidad se confirman por email en cada ciclo; nada se envía ni se cobra sin tu OK.</p>
</section>

<section class="container comparison">
  <div class="comparison-grid">
    <div class="comparison-card">
      <h3>Pack por suscripción</h3>
      <ul>
        <li>Definido al inicio (ej. proteína + creatina).</li>
        <li>Se repite igual cada periodo.</li>
        <li>Pausas o cambias entrando en tu cuenta.</li>
      </ul>
    </div>
    <div class="comparison-card">
      <h3>AlphaBox</h3>
      <ul>
        <li>Plan (Básica/Pro/Elite) con recordatorio antes de cada envío.</li>
        <li>Te preguntamos: “¿así está bien o quieres ajustar?”</li>
        <li>Solo se envía cuando confirmas por email.</li>
      </ul>
    </div>
  </div>
</section>

<section class="why-us container">
  <div class="why-grid">
    <div class="why-item">
      <div class="icon">📦</div>
      <h3>Reposición programada, siempre confirmada</h3>
      <p>Configura cantidades y frecuencia y confirma cada envío antes de cobrarlo. Puedes pausar o editar en un clic.</p>
    </div>
    <div class="why-item">
      <div class="icon">💸</div>
      <h3>Descuento recurrente para suscriptores</h3>
      <p>El precio de suscripción aplica mientras sigas activo. Si hay cambios de tarifa, te avisamos antes de renovar.</p>
    </div>
    <div class="why-item">
      <div class="icon">⚡</div>
      <h3>Envíos habituales según stock</h3>
      <p>Enviamos en 24/48h cuando hay stock y el transportista lo permite. Si prevemos retrasos, te avisamos antes de que confirmes el pedido.</p>
    </div>
    <div class="why-item">
      <div class="icon">⏳</div>
      <h3>Piloto con plazas limitadas</h3>
      <p>Abrimos plazas poco a poco para asegurar stock y soporte cercano. Si está lleno, puedes apuntarte a la lista de aviso.</p>
    </div>
  </div>
</section>

<section class="pricing container">
  <h2>Planes Optimizados para Tu Rendimiento</h2>

  <div class="plans">
    <div class="plan">
      <h3>AlphaBox Básica</h3>
      <p>Proteína + Creatina</p>
      <p class="price">€34.99/mes</p>
      <p class="saving">Ahorro orientativo frente a compra suelta (según precios actuales).</p>
      <button class="cta-btn big plan-btn" type="button" data-plan="basica">Quiero activar AlphaBox</button>
    </div>

    <div class="plan featured">
      <h3>AlphaBox Pro</h3>
      <p>Proteína + Creatina + Omega 3</p>
      <p class="price">€44.99/mes</p>
      <p class="saving">Ahorro orientativo frente a compra suelta (según precios actuales).</p>
      <span class="badge">Más Popular</span>
      <button class="cta-btn big plan-btn" type="button" data-plan="pro">Quiero activar AlphaBox</button>
    </div>

    <div class="plan">
      <h3>AlphaBox Elite ⚡</h3>
      <p>Todo lo básico + pre entreno ligero</p>
      <p class="price">€59.99/mes</p>
      <p class="saving">Ahorro orientativo frente a compra suelta (según precios actuales).</p>
      <button class="cta-btn big plan-btn" type="button" data-plan="elite">Quiero activar AlphaBox</button>
    </div>
  </div>

  <div class="urgency-notice" style="text-align: center; margin-top: 2rem; padding: 1rem; background: var(--bg-panel); border-radius: var(--radius);">
    <p style="color: var(--accent); font-weight: bold;">⏰ Aviso de disponibilidad</p>
    <p>Las condiciones y descuentos pueden variar según stock. Te avisaremos siempre antes de activar o renovar tu plan.</p>
  </div>
</section>

<section class="container interest-box" id="alphabox-form">
  <h2>Activa tu AlphaBox y ajusta detalles</h2>
  <p class="section-hint">Enviamos tu solicitud con plan y frecuencia. Ajustamos cantidades y detalles por email antes de activar cualquier envío o cobro.</p>
  <form class="interest-form" id="alphaboxInterestForm">
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="user_id" value="<?= $userId ? (int) $userId : '' ?>">
    <div class="form-grid">
      <label>
        Nombre
        <input type="text" name="name" placeholder="Tu nombre" required>
      </label>
      <label>
        Email
        <input type="email" name="email" placeholder="tuemail@ejemplo.com" required>
      </label>
      <label>
        Plan preferido
        <select name="plan" id="planSelect">
          <option value="basica">AlphaBox Básica</option>
          <option value="pro">AlphaBox Pro</option>
          <option value="elite">AlphaBox Elite</option>
        </select>
      </label>
      <label>
        Frecuencia deseada
        <select name="frequency">
          <option value="mensual">Mensual</option>
          <option value="cada_2_meses">Cada 2 meses</option>
          <option value="cada_3_meses">Cada 3 meses</option>
        </select>
      </label>
    </div>
    <label class="full-width">
      Comentarios (opcional)
      <textarea name="notes" rows="3" placeholder="Ej: sin estimulantes, avisar antes de Omega 3, alergias…"></textarea>
    </label>
    <button type="submit" class="cta-btn submit-btn">Enviar solicitud</button>
    <p class="form-note">Nota: hoy los ajustes finos (cantidades exactas) se gestionan manualmente por email hasta habilitar el panel.</p>
    <p id="alphaboxStatus" class="form-status" role="status" aria-live="polite"></p>
  </form>
</section>

</main>

<?php require_once '../components/footer.php'; ?>

<script>
(function() {
  const buttons = document.querySelectorAll('.plan-btn');
  const planSelect = document.getElementById('planSelect');
  const form = document.getElementById('alphaboxInterestForm');
  const status = document.getElementById('alphaboxStatus');

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      if (planSelect) planSelect.value = btn.dataset.plan || 'pro';
      document.getElementById('alphabox-form')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (status) {
      status.textContent = 'Enviando…';
      status.className = 'form-status pending';
    }

    const formData = new FormData(form);
    const clean = (v) => (v || '').toString().trim();
    const name = clean(formData.get('name'));
    const email = clean(formData.get('email'));
    const plan = clean(formData.get('plan'));
    const freq = clean(formData.get('frequency'));
    const notes = clean(formData.get('notes'));

    if (!name || !email) {
      if (status) {
        status.textContent = 'Completa nombre y email.';
        status.className = 'form-status error';
      }
      return;
    }

    formData.set('name', name);
    formData.set('email', email);

    try {
      const res = await fetch('../controllers/alphabox.php', {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' }
      });

      const data = await res.json().catch(async () => ({ success: false, message: await res.text() }));

      if (data.success) {
        if (status) {
          const extra = data.email_sent ? '' : ' (si no ves el email, revisa spam o responde para confirmar)';
          status.textContent = (data.message || 'Solicitud recibida.') + extra;
          status.className = 'form-status success';
        }
        form.reset();
        if (planSelect) planSelect.value = plan;
      } else {
        throw new Error(data.message || 'No pudimos registrar tu solicitud.');
      }
    } catch (err) {
      if (status) {
        status.textContent = err?.message || 'Error al enviar. Intenta de nuevo o escríbenos.';
        status.className = 'form-status error';
      }
    }
  });
})();
</script>
