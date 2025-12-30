<?php
$isAdminPanel = true;
require_once '../models/user.php';
require_once '../components/head.php';

$currentUserId = (int)($_SESSION['user']['id'] ?? 0);
if (!$currentUserId) {
  header('Location: ../login.php');
  exit;
}

$profile = getUserById($currentUserId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
  $name  = trim($_POST['name']);
  $email = trim($_POST['email']);
  if (updateUser($currentUserId, $name, $email)) {
    header("Location: profile.php?saved=1");
    exit;
  } else {
    $profileError = "No se pudo actualizar el perfil.";
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $currentPassword = $_POST['current_password'] ?? '';
  $newPassword     = $_POST['new_password'] ?? '';
  $repeatPassword  = $_POST['repeat_password'] ?? '';

  if ($newPassword !== $repeatPassword) {
    $passwordError = "Las contraseñas nuevas no coinciden.";
  } else {
    if (updatePassword($currentUserId, $currentPassword, $newPassword)) {
      header("Location: profile.php?pwd_changed=1");
      exit;
    } else {
      $passwordError = "Contraseña actual incorrecta o error al actualizar.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>
<main class="admin-dashboard">
  <h1 class="admin-title">👤 Perfil del administrador</h1>

  <?php if(isset($_GET['saved'])): ?>
    <div class="admin-alert success">✅ Perfil actualizado correctamente.</div>
  <?php endif; ?>

  <?php if(isset($_GET['pwd_changed'])): ?>
    <div class="admin-alert success">🔒 Contraseña cambiada correctamente.</div>
  <?php endif; ?>

  <?php if(!empty($profileError)): ?>
    <div class="admin-alert"><?= htmlspecialchars($profileError) ?></div>
  <?php endif; ?>

  <section class="profile-section">
    <form method="POST" class="admin-form table-style admin-card">
      <h2 class="form-title admin-title">Datos básicos</h2>
      <div class="form-row">
        <label class="title">Nombre</label>
        <input type="text" name="name" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label class="title">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>
      </div>

      <div class="form-row form-actions">
        <button type="submit" name="save_profile" class="btn">Guardar cambios</button>
      </div>
    </form>
    <form method="POST" class="admin-form table-style admin-card">
      <h2 class="form-title admin-title">Cambiar contraseña</h2>

      <?php if(!empty($passwordError)): ?>
        <div class="admin-alert"><?= htmlspecialchars($passwordError) ?></div>
      <?php endif; ?>

      <div class="form-row">
        <label class="title">Contraseña actual</label>
        <input type="password" name="current_password" required>
      </div>

      <div class="form-row">
        <label class="title">Nueva contraseña</label>
        <input type="password" name="new_password" required>
      </div>

      <div class="form-row">
        <label class="title">Repetir nueva contraseña</label>
        <input type="password" name="repeat_password" required>
      </div>

      <div class="form-row form-actions">
        <button type="submit" name="change_password" class="btn">Actualizar contraseña</button>
      </div>
    </form>
  </section>
</main>
</body>
</html>
