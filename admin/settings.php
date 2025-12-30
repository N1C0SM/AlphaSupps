<?php
require_once '../components/head.php';
$isAdminPanel = true;

require_once '../controllers/update_settings.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$settings = getSettings();
if (!$settings) {
    // Si no hay configuración, redirigir con error
    header("Location: settings.php?error=no_settings");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>
<main class="admin-dashboard">
<h1 class="admin-title">⚙️ Configuración general</h1>
<div class="admin-card admin-form table-wrapper">
  <table class="admin-table">
  <thead>
  <tr>
    <th>Campo</th>
    <th>Valor editable</th>
  </tr>
  </thead>
  <tbody>

  <tr>
    <td class="title">Nombre del sitio</td>
    <td>
      <input type="text" id="nombreInput" class="title" value="<?= htmlspecialchars($settings['site_name']) ?>">
    </td>
  </tr>

  <tr>
    <td class="title">Email de contacto</td>
    <td>
      <input type="email" id="emailInput" class="title" value="<?= htmlspecialchars($settings['contact_email']) ?>" >
    </td>
  </tr>

  <tr>
    <td class="title">Color principal</td>
    <td>
      <div style="display:flex;align-items:center;gap:12px;">

        <div class="color-picker-table">
          <div
            class="color-preview-table"
            id="colorPreviewTable"
            style="background: <?= htmlspecialchars($settings['accent_color']) ?>;"
          ></div>

          <input
            type="color"
            id="colorInputTable"
            class="color-input-native"
            value="<?= htmlspecialchars($settings['accent_color']) ?>"
          >
        </div>

        <code id="colorHexTable"  class="title"><?= htmlspecialchars($settings['accent_color']) ?></code>

      </div>
    </td>
  </tr>

  <tr>
    <td class="title">Modo Stripe</td>
    <td>
      <select id="stripeModeInput">
        <option value="test" <?= ($settings['stripe_mode']==='test') ? 'selected' : '' ?>>Test</option>
        <option value="live" <?= ($settings['stripe_mode']==='live') ? 'selected' : '' ?>>Live</option>
      </select>
    </td>
  </tr>

  </tbody>
  </table>
</div>
<form action="<?= BASE_URL; ?>controllers/update_settings.php" method="POST" id="hiddenForm">

  <input type="hidden" name="site_name" id="hiddenNombre">
  <input type="hidden" name="contact_email" id="hiddenEmail">
  <input type="hidden" name="accent_color" id="hiddenColor">
  <input type="hidden" name="stripe_mode" id="hiddenStripe">

  <div class="admin-card">
    <div class="buttons">
      <button type="submit" class="btn">💾 Guardar cambios</button>
      <button type="submit" name="reset_defaults" value="1" class="btn btn-warning">
        ♻️ Restaurar valores por defecto
      </button>
    </div>
  </div>

</form>

</main>

<script>
function syncAll() {
  document.getElementById("hiddenNombre").value =
    document.getElementById("nombreInput").value;

  document.getElementById("hiddenEmail").value =
    document.getElementById("emailInput").value;

  document.getElementById("hiddenColor").value =
    document.getElementById("colorInputTable").value;

  document.getElementById("hiddenStripe").value =
    document.getElementById("stripeModeInput").value;
}

document.querySelectorAll("#nombreInput, #emailInput, #stripeModeInput")
.forEach(el => el.addEventListener("input", syncAll));

document.getElementById("colorInputTable").addEventListener("input", (e) => {
  const newColor = e.target.value;

  document.getElementById("colorPreviewTable").style.background = newColor;
  document.getElementById("colorHexTable").textContent = newColor;

  syncAll();
});

syncAll();
</script>

</body>
</html>
