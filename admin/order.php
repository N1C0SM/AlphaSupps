<?php
$isAdminPanel = true;
 require_once '../components/head.php';

require_once '../models/user.php';
require_once '../models/order.php';
require_once '../models/db.php';

// Verificar sesión de administrador
$currentAdmin = $_SESSION['user'] ?? null;
if (!$currentAdmin || $currentAdmin['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ID del pedido
$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    die('❌ Pedido no especificado.');
}

// Pedido
$order = getOrderById($orderId);
if (!$order) {
    die('❌ Pedido no encontrado.');
}

// Usuario relacionado
$user = getUserByEmail($order['email']);

// =========================
//   TOGGLE has_sent
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle_status"])) {

    $id = $_POST["id"];
    $currentSent = (int) $_POST["current_status"];

    // Alternar 0 ↔ 1
    $newSent = $currentSent === 1 ? 0 : 1;

    $conn = connectToDatabase();
    $stmt = $conn->prepare("UPDATE orders SET has_sent = ? WHERE id = ?");
    $stmt->bind_param("ii", $newSent, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: order.php?id=$id&status_updated=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>

<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">

<h1 class="admin-title">📦 Pedido #<?= $order['id'] ?></h1>

<?php if(isset($_GET['status_updated'])): ?>
  <div class="admin-alert success">✔ Estado actualizado correctamente.</div>
<?php endif; ?>

<section class="stats-grid">

  <!-- Usuario -->
  <div class="card">
    <h3 >Cliente</h3>
    <p class="admin-title"><?= htmlspecialchars($order['name']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
  </div>

  <!-- Estado con flip -->
  <div class="card" style="display:flex;flex-direction:column;align-items:center;gap:12px;">
    <h3>Envío</h3>

    <div class="status-card" onclick="flipStatus()">
      <div class="status-inner" id="statusFlip">

        <!-- FRONT (estado actual) -->
        <div class="status-front <?= $order['has_sent'] == 1 ? 'done' : '' ?>">
          <?= $order['has_sent'] == 1 ? 'ENVIADO' : 'NO ENVIADO' ?>
        </div>

        <!-- BACK (estado opuesto) -->
        <div class="status-back">
          <?= $order['has_sent'] == 1 ? 'NO ENVIADO' : 'ENVIADO' ?>
        </div>

      </div>
    </div>

    <form id="statusForm" method="POST" style="display:none;">
      <input type="hidden" name="toggle_status" value="1">
      <input type="hidden" name="id" value="<?= $order['id'] ?>">
      <input type="hidden" name="current_status" value="<?= $order['has_sent'] ?>">
    </form>
  </div>

  <!-- Fecha -->
  <div class="card">
    <h3 class="title">Fecha</h3>
    <p><?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
  </div>

</section>

<h2 style="margin-top:3rem;" class="admin-title">🛒 Detalles del pedido</h2>

<div class="admin-card table-wrapper">
  <table class="admin-table">
    <tbody>
      <tr>
        <td class="title">Producto</td>
        <td class="title">
          <?= htmlspecialchars($order['product']) ?>
          <?php
            $lineas = array_map('trim', explode(',', $order['product']));
            $detalle = [];
            foreach ($lineas as $linea) {
              if (preg_match('/\\[incluye:(.+)\\]/i', $linea, $m)) {
                $detalle[] = trim($m[1]);
              }
            }
            if (!empty($detalle)):
          ?>
            <div class="order-pack-detail">
              <strong>Detalle pack:</strong>
              <ul>
                <?php foreach ($detalle as $d): ?>
                  <li><?= htmlspecialchars($d) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td class="title">Cantidad</td>
        <td class="title"><?= (int)$order['quantity'] ?></td>
      </tr>
      <tr>
        <td class="title">Total</td>
        <td class="title">€<?= number_format($order['price'], 2) ?></td>
      </tr>
    </tbody>
  </table>
</div>

<div class="row-actions" style="margin-top:1rem;">
  <a href="./orders.php" class="action-btn ghost">⬅ Volver a pedidos</a>
  <a href="../views/invoice.php?id=<?= $order['id'] ?>" target="_blank" class="action-btn ghost">🧾 Ver factura</a>
</div>

</main>

<script>
function flipStatus() {
  const card = document.getElementById("statusFlip");
  card.parentElement.classList.add("status-active");

  setTimeout(() => {
    document.getElementById("statusForm").submit();
  }, 350);
}
</script>

</body>
</html>
