<?php
$isAdminPanel = true;
require_once '../models/newsletter.php';
require_once '../models/prelaunch.php';
require_once '../components/head.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$newsletterSubscribers = getAllSubscribers();
$prelaunchRegisters    = getAllPrelaunchRegisters();
?>
<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">
  <h1 class="admin-title">📨 Newsletter & Prelanzamiento</h1>

  <section>
    <h2 class="admin-title">Suscriptores Newsletter</h2>
    <?php if (empty($newsletterSubscribers)): ?>
      <div class="admin-alert">No hay suscriptores por el momento.</div>
    <?php else: ?>
      <div class="admin-card table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($newsletterSubscribers as $subscriber): ?>
            <tr>
              <td><?= $subscriber['id'] ?></td>
              <td class="title"><?= htmlspecialchars($subscriber['email']) ?></td>
              <td class="title"><?= $subscriber['created_at'] ?? 'N/A' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <!-- Registros Prelanzamiento -->
  <section>
    <h2 class="admin-title">Registros Prelanzamiento</h2>
    <?php if (empty($prelaunchRegisters)): ?>
      <div class="admin-alert">No hay registros de prelanzamiento por el momento.</div>
    <?php else: ?>
      <div class="admin-card table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($prelaunchRegisters as $register): ?>
            <tr>
              <td class="title"><?= $register['id'] ?></td>
              <td class="title"><?= htmlspecialchars($register['name']) ?></td>
              <td class="title"><?= htmlspecialchars($register['email']) ?></td>
              <td class="title"><?= $register['register_date'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
