<?php
$isAdminPanel = true;

require_once '../models/user.php';
require_once '../models/db.php';
require_once '../components/head.php';

$conn = connectToDatabase();

// Comprobar admin logueado
$currentAdmin = $_SESSION['user'] ?? null;
if (!$currentAdmin || $currentAdmin['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

/* ==========================
   CAMBIO DE ROL AUTOMÁTICO
   ========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auto_role'])) {

  $id   = $_POST['id'] ?? null;
  $role = $_POST['role'] ?? null;

  if ($role !== 'admin') {
    $role = null; // usuario normal
  }

  if ($id) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    $stmt->execute();
    $stmt->close();
  }

  header("Location: users.php?updated=1");
  exit;
}
$users = getAllUsers($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<?php require_once '../components/header.php'; ?>
<main class="admin-dashboard">
  <h1 class="admin-title">👥 Usuarios</h1>
  <?php if(isset($_GET['updated'])): ?>
    <div class="admin-alert success">✔ Rol actualizado.</div>
  <?php endif; ?>
  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $user): ?>
        <tr>
          <td class="title"><?= $user['id'] ?></td>
          <td class="title"><?= htmlspecialchars($user['name']) ?></td>
          <td class="title"><?= htmlspecialchars($user['email']) ?></td>
          <td>
            <form method="POST" action="users.php" class="auto-form">
              <input type="hidden" name="auto_role" value="1">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <select name="role" onchange="this.form.submit()">
                <option value="" <?= empty($user['role']) ? 'selected' : '' ?>>Usuario</option>
                <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
              </select>
            </form>
          </td>
          <td class="title"><?= $user['created_at'] ?></td>
          <td>
            <a href="./user.php?id=<?= $user['id'] ?>" class="action-btn ghost">👁 Ver</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
