<?php

$isAdminPanel = true;
require_once '../models/db.php';
require_once '../models/newsletter.php';
require_once '../components/head.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$subs = getAllSubscribers();
?>
<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">
  <div class="admin-header">
    <h1 class="admin-title">📬 Suscriptores</h1>
    <a href="../controllers/export_newsletter.php" class="btn btn-primary">⬇️ Exportar CSV</a>
  </div>

  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Email</th><th>Fecha de suscripción</th></tr></thead>
      <tbody>
        <?php foreach ($subs as $s): ?>
          <tr>
            <td class="title"><?= $s['id'] ?></td>
            <td class="title"><?= htmlspecialchars($s['email']) ?></td>
            <td class="title"><?= htmlspecialchars($s['subscribed_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
