<?php
$isAdminPanel = true;

require_once '../models/order.php';
require_once '../models/user.php';

$currentAdmin = $_SESSION['user'] ?? null;
if (!$currentAdmin || $currentAdmin['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$email = $_GET['email'] ?? null;
if (!$email) {
  die('<p style="color:red;">❌ Email no especificado.</p>');
}

$orders = getOrdersByEmail($email);
$user   = getUserByEmail($email);
?>
<!DOCTYPE html>
<html lang="es">

<?php require_once '../components/head.php'; ?>

<body>
<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">

  <h1>🧾 Facturas de usuario</h1>

  <div class="stats-grid">
    <div class="card">
      <h3>Usuario</h3>
      <p class="ellipsis"><?= htmlspecialchars($user['name'] ?? 'Desconocido') ?></p>
    </div>

    <div class="card">
      <h3>Email</h3>
      <p class="ellipsis"><?= htmlspecialchars($email) ?></p>
    </div>

    <div class="card">
      <h3>Total de pedidos</h3>
      <p><?= count($orders) ?></p>
    </div>
  </div>

  <h2 class="form-title" style="margin-top:2rem;">📑 Listado de facturas</h2>

  <?php if (empty($orders)): ?>
    <p class="admin-alert">⚠️ No hay facturas porque este usuario no tiene pedidos.</p>
  <?php else: ?>
    <div class="admin-card table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th># Pedido</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Factura</th>
            <th>Acción</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td class="title">#<?= $o['id'] ?></td>
              <td class="title"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
              <td class="title">€<?= number_format($o['price'], 2) ?></td>
              <td>
                <a href="./factura.php?id=<?= $o['id'] ?>" target="_blank" class="action-btn ghost">
                  📄 Ver factura
                </a>
              </td>
              <td>
                <a href="./order.php?id=<?= $o['id'] ?>" class="action-btn ghost">👁 Ver pedido</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <a href="./users.php" class="btn" style="margin-top:1rem;">⬅ Volver a usuarios</a>

</main>

</body>
</html>
