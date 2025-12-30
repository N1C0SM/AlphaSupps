<?php
$isAdminPanel = true;
require_once '../models/order.php';
require_once '../components/head.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$orders = getAllOrders();
?>
<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">
  <h1 class="admin-title">📦 Pedidos</h1>

  <?php if (empty($orders)): ?>
    <p class="admin-alert">⚠️ No hay pedidos registrados todavía.</p>
  <?php else: ?>
    <div class="orders-card table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="th-toggle"></th>
            <th>Cliente</th>
            <th>Producto(s)</th>
            <th>Cantidad</th>
            <th>Precio total</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <?php
              $lineas = array_map('trim', explode(',', $order['product']));
              $products = [];
              foreach ($lineas as $lineaRaw) {
                $linea = trim($lineaRaw);
                $packItems = [];
                if (preg_match('/\\[incluye:(.+)\\]/i', $linea, $m)) {
                  $packItems = array_filter(array_map('trim', explode('|', $m[1])));
                  $linea = trim(str_replace($m[0], '', $linea));
                }
                $products[] = [
                  'name' => $linea,
                  'packItems' => $packItems
                ];
              }
              $hasPack = array_reduce($products, fn($carry, $p) => $carry || !empty($p['packItems']), false);
              $detailId = 'detail-' . $order['id'];
            ?>
            <tr>
              <td class="title toggle-cell" data-label="Detalle">
                <?php if ($hasPack): ?>
                  <button class="row-toggle" data-target="<?= $detailId ?>" aria-expanded="false" aria-controls="<?= $detailId ?>">▸</button>
                <?php else: ?>
                  <span class="no-toggle">—</span>
                <?php endif; ?>
              </td>
              <td class="title" data-label="Cliente"><?= htmlspecialchars($order['name']) ?></td>
              <td class="title" data-label="Producto(s)">
                <div class="product-lines">
                  <?php foreach ($products as $product): ?>
                    <div class="product-line">
                      <span><?= htmlspecialchars($product['name']) ?></span>
                      <?php if (!empty($product['packItems'])): ?>
                        <span class="product-pack-tag">Pack</span>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </td>
              <td class="title"><?= (int)$order['quantity'] ?></td>
              <td class="title" data-label="Precio total">€<?= number_format($order['price'], 2) ?></td>
              <td class="title" data-label="Fecha"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
              <td data-label="Acciones">
                <div class="actions-row">
                  <a href="./order.php?id=<?= $order['id'] ?>" class="action-btn ghost">👁 Ver</a>
                  <a href="../views/invoice.php?id=<?= $order['id'] ?>" class="action-btn ghost" target="_blank">🧾 Factura</a>
                </div>
              </td>
            </tr>
            <?php if ($hasPack): ?>
              <tr id="<?= $detailId ?>" class="order-detail-row" hidden>
                <td></td>
                <td colspan="6">
                  <div class="order-detail-box">
                    <div class="detail-head">
                      <div>
                        <p class="detail-label">Pedido #<?= $order['id'] ?></p>
                        <div class="detail-title"><?= htmlspecialchars($order['name']) ?></div>
                        <p class="detail-sub"><?= htmlspecialchars($order['email']) ?></p>
                      </div>
                      <div class="detail-chips">
                        <span class="detail-chip <?= $order['has_sent'] ? 'success' : 'warning' ?>">
                          <?= $order['has_sent'] ? 'Enviado' : 'Pendiente de envío' ?>
                        </span>
                        <span class="detail-chip soft">Pago <?= htmlspecialchars($order['payment_id']) ?></span>
                      </div>
                    </div>

                    <div class="detail-meta-grid">
                      <div class="detail-meta-card">
                        <span class="detail-label">Cantidad</span>
                        <div class="detail-value"><?= (int)$order['quantity'] ?> uds</div>
                      </div>
                      <div class="detail-meta-card">
                        <span class="detail-label">Total</span>
                        <div class="detail-value">€<?= number_format($order['price'], 2) ?></div>
                        <?php if (!empty($order['gift'])): ?>
                          <span class="detail-chip ghost small">🎁 <?= htmlspecialchars($order['gift']) ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="detail-meta-card">
                        <span class="detail-label">Dirección</span>
                        <div class="detail-value line-clamp"><?= htmlspecialchars($order['address']) ?></div>
                        <p class="detail-sub">CP <?= htmlspecialchars($order['postal_code']) ?></p>
                      </div>
                      <div class="detail-meta-card">
                        <span class="detail-label">Contacto</span>
                        <div class="detail-value"><?= htmlspecialchars($order['phone'] ?: 'N/A') ?></div>
                        <p class="detail-sub">Creado el <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
                      </div>
                    </div>

                    <div class="detail-products">
                      <p class="detail-subtitle">Productos y packs</p>
                      <ul class="detail-products-list">
                        <?php foreach ($products as $product): ?>
                          <li class="detail-product">
                            <div class="detail-line"><?= htmlspecialchars($product['name']) ?></div>
                            <?php if (!empty($product['packItems'])): ?>
                              <div class="pack-items">
                                <span class="detail-label">Incluye</span>
                                <ul class="pack-items-list">
                                  <?php foreach ($product['packItems'] as $pi): ?>
                                    <li class="pack-item">
                                      <div class="detail-line"><?= htmlspecialchars($pi) ?></div>
                                      <p class="detail-sub">Artículo del pack</p>
                                    </li>
                                  <?php endforeach; ?>
                                </ul>
                              </div>
                            <?php endif; ?>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<script>
document.querySelectorAll('.row-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = document.getElementById(btn.dataset.target);
    if (!target) return;
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    btn.textContent = expanded ? '▸' : '▾';
    target.hidden = expanded;
  });
});
</script>
</body>
</html>
