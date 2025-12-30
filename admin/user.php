<?php
$isAdminPanel = true;

require_once '../models/user.php';
require_once '../models/order.php';
require_once '../models/newsletter.php';
require_once '../models/prelaunch.php';
require_once '../components/head.php';

$conn = connectToDatabase();

$currentAdmin = $_SESSION['user'] ?? null;
if (!$currentAdmin || $currentAdmin['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$userId = $_GET['id'] ?? null;
if (!$userId) {
  die('❌ Usuario no especificado.');
}

$profile = getUserById($userId, $conn);
if (!$profile) {
  die('❌ Usuario no encontrado.');
}

$orders = getOrdersByEmail($profile['email']);


$newsletter = getSubscriberByEmail($profile['email'], $conn);
$prelaunch  = getPrelaunchByEmail($profile['email']);

$totalOrders = count($orders);
$totalSpent = 0;
$lastOrderDate = null;
$lastOrderProduct = null;

foreach ($orders as $o) {
  $totalSpent += (float) $o['price'];
  if ($lastOrderDate === null || strtotime($o['created_at']) > strtotime($lastOrderDate)) {
    $lastOrderDate = $o['created_at'];
    $lastOrderProduct = $o['product'];
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle_role"])) {

    $id = $_POST["id"];
    $currentRole = $_POST["current_role"];

    $newRole = ($currentRole === "admin") ? null : "admin";

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $newRole, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: user.php?id=" . $id . "&role_updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>
<main class="admin-dashboard">
  <h1 class="admin-title">👤 Usuario</h1>
  <?php if(isset($_GET['role_updated'])): ?>
    <div class="admin-alert success">✔ Rol actualizado automáticamente.</div>
  <?php endif; ?>
  <div class="order-detail-box user-detail-box">
    <div class="detail-head">
      <div>
        <p class="detail-label">Usuario #<?= $profile['id'] ?></p>
        <div class="detail-title"><?= htmlspecialchars($profile['name']) ?></div>
        <p class="detail-sub ellipsis"><?= htmlspecialchars($profile['email']) ?></p>
      </div>
      <div class="detail-chips">
        <span class="detail-chip <?= $profile['role'] === 'admin' ? 'success' : 'ghost' ?>">
          Rol: <?= $profile['role'] === 'admin' ? 'Admin' : 'Usuario' ?>
        </span>
        <span class="detail-chip soft">Alta <?= date('d/m/Y', strtotime($profile['created_at'])) ?></span>
        <span class="detail-chip ghost">Pedidos <?= $totalOrders ?></span>
      </div>
    </div>

    <div class="detail-meta-grid compact">
      <div class="detail-meta-card">
        <span class="detail-label">Pedidos</span>
        <div class="detail-value"><?= $totalOrders ?></div>
        <p class="detail-sub">
          <?= $lastOrderDate ? 'Último el ' . date('d/m/Y', strtotime($lastOrderDate)) : 'Sin pedidos todavía' ?>
        </p>
      </div>
      <div class="detail-meta-card">
        <span class="detail-label">Importe acumulado</span>
        <div class="detail-value">€<?= number_format($totalSpent, 2) ?></div>
        <?php if ($lastOrderProduct): ?>
          <p class="detail-sub ellipsis"><?= htmlspecialchars($lastOrderProduct) ?></p>
        <?php endif; ?>
      </div>
      <div class="detail-meta-card role-card">
        <span class="detail-label">Rol</span>
        <div class="flip-container small" onclick="toggleRoleFlip()">
          <div class="flip-inner" id="roleFlip">
            <div class="flip-front <?= $profile['role'] === 'admin' ? 'admin' : '' ?>">
              <?= $profile['role'] === 'admin' ? 'ADMIN' : 'USUARIO' ?>
            </div>
            <div class="flip-back">
              Cambiando...
            </div>
          </div>
        </div>
        <form id="roleForm" method="POST" class="hidden">
          <input type="hidden" name="toggle_role" value="1">
          <input type="hidden" name="id" value="<?= $profile['id'] ?>">
          <input type="hidden" name="current_role" value="<?= $profile['role'] ?>">
        </form>
      </div>
      <div class="detail-meta-card">
        <span class="detail-label">Suscripciones</span>
        <div class="detail-chips">
          <span class="detail-chip <?= $newsletter ? 'success' : 'ghost' ?>">
            Newsletter <?= $newsletter ? 'sí' : 'no' ?>
          </span>
          <span class="detail-chip <?= $prelaunch ? 'success' : 'ghost' ?>">
            Prelanzamiento <?= $prelaunch ? 'sí' : 'no' ?>
          </span>
        </div>
        <?php if ($newsletter): ?>
          <p class="detail-sub">Desde <?= date('d/m/Y', strtotime($newsletter['subscribed_at'])) ?></p>
        <?php endif; ?>
        <?php if ($prelaunch): ?>
          <p class="detail-sub">Desde <?= date('d/m/Y', strtotime($prelaunch['register_date'])) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <h2 class="admin-title table-title">📦 Pedidos del usuario</h2>
  <?php if (empty($orders)): ?>
    <p class="admin-alert ">⚠️ Este usuario no tiene pedidos.</p>
  <?php else: ?>
    <div class="admin-card table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td class="title">#<?= $o['id'] ?></td>
            <td class="title"><?= htmlspecialchars($o['product']) ?></td>
            <td class="title"><?= (int)$o['quantity'] ?></td>
            <td class="title">€<?= number_format($o['price'], 2) ?></td>
            <td class="title"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
            <td>
              <div class="row-actions">
                <a href="./order.php?id=<?= $o['id'] ?>" class="action-btn ghost">👁 Pedido</a>
                <a href="../views/invoice.php?id=<?= $o['id'] ?>" target="_blank" class="action-btn ghost">🧾 Factura</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
  <h2 class="admin-title table-title">📬 Suscripciones</h2>
  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <tbody>
        <tr>
          <td class="title">Newsletter</td>
          <td><?= $newsletter ? 'Sí ('.$newsletter['subscribed_at'].')' : 'No' ?></td>
        </tr>
        <tr>
          <td class="title">Prelanzamiento</td>
          <td><?= $prelaunch ? 'Sí ('.$prelaunch['register_date'].')' : 'No' ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="row-actions page-actions">
    <a href="./users.php" class="btn">⬅ Volver a usuarios</a>
    <a href="./invoices_user.php?email=<?= urlencode($profile['email']) ?>" class="btn btn-ghost">🧾 Ver todas las facturas</a>
  </div>
</main>
<script>
function toggleRoleFlip() {
  const card = document.getElementById("roleFlip");
  card.classList.add("flip-active");

  // Espera a mitad del giro para enviar el formulario automáticamente
  setTimeout(() => {
    document.getElementById("roleForm").submit();
  }, 300);
}
</script>
</body>
</html>
