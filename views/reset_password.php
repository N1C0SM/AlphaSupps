
<style>
/* Importar estilos del login para consistencia */
@import url('../css/login.css');
</style>
<?php
$conexion = new mysqli(
    "mysql-alphasupps.alwaysdata.net",
    "432149",
    "Dxq954pA",
    "alphasupps_db",
    3306
);

$mensaje = "";
$modoFormulario = false;

// ============================
// VALIDAR TOKEN
// ============================
if (isset($_GET["token"])) {

    $token = $_GET["token"];

    $stmt = $conexion->prepare(
        "SELECT id, reset_expires FROM users WHERE reset_token = ? LIMIT 1"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (strtotime($usuario["reset_expires"]) > time()) {
            $modoFormulario = true;
        } else {
            $mensaje = "El enlace ha expirado. Solicita uno nuevo.";
        }

    } else {
        $mensaje = "El token no es válido.";
    }
}

// ============================
// PROCESAR RESTABLECIMIENTO
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["token"];
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    if ($password !== $password2) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {

        // Buscar usuario por token
        $stmt = $conexion->prepare(
            "SELECT id FROM users WHERE reset_token = ? LIMIT 1"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {

            $user = $res->fetch_assoc();
            $id = $user["id"];

            // Hash seguro
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);

            // Actualizar contraseña y limpiar token
            $stmt2 = $conexion->prepare(
                "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?"
            );
            $stmt2->bind_param("si", $nuevoHash, $id);
            $stmt2->execute();

            $mensaje = "Tu contraseña ha sido actualizada correctamente. Ahora puedes iniciar sesión.";
            $modoFormulario = false;

        } else {
            $mensaje = "Token no válido.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<?php require_once '../components/head.php'; ?>
<?php require_once '../components/cookies.php'; ?>

<body>
<?php require_once '../components/header.php'; ?>

<main class="home">

  <section class="hero">
    <h1>Nueva Contraseña Segura</h1>
    <p class="section-subtitle">
      Crea una contraseña fuerte para proteger tu cuenta. Recomendamos al menos 8 caracteres con números y símbolos.
    </p>

    <?php if ($mensaje): ?>
      <?php if (strpos($mensaje, 'correctamente') !== false): ?>
        <div class="login-success">
          ✅ <?= htmlspecialchars($mensaje) ?>
        </div>
      <?php else: ?>
        <div class="login-error">
          ⚠️ <?= htmlspecialchars($mensaje) ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($modoFormulario): ?>
      <div class="login-card">
        <form method="POST" class="login-form" action="">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <label for="password">Nueva contraseña</label>
          <div class="password-input-container">
            <input type="password" name="password" id="password" placeholder="••••••••" required minlength="8">
            <button type="button" class="password-toggle" id="password-toggle" title="Mostrar contraseña">
              <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
            </button>
          </div>

          <label for="password2">Confirmar nueva contraseña</label>
          <div class="password-input-container">
            <input type="password" name="password2" id="password2" placeholder="••••••••" required minlength="8">
            <button type="button" class="password-toggle" id="confirm-toggle" title="Mostrar contraseña">
              <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
            </button>
          </div>

          <button type="submit" class="btn btn-primary btn-lg">🔐 Actualizar Contraseña</button>
        </form>

        <div class="login-link">
          <a href="login.php">← Volver al inicio de sesión</a>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <section class="why-us">
    <h2>Tu seguridad es nuestra prioridad</h2>
    <div class="why-grid">
      <div class="why-item">
        <div class="icon">🔐</div>
        <h3>Encriptación SSL</h3>
        <p>Tus datos se transmiten de forma segura a través de conexiones encriptadas SSL de 256 bits.</p>
      </div>
      <div class="why-item">
        <div class="icon">🛡️</div>
        <h3>Hash seguro bcrypt</h3>
        <p>Tus contraseñas se almacenan usando el algoritmo bcrypt con sal aleatoria para máxima seguridad.</p>
      </div>
      <div class="why-item">
        <div class="icon">⚡</div>
        <h3>Tiempo limitado</h3>
        <p>Los enlaces de restablecimiento tienen una validez limitada para evitar accesos no autorizados.</p>
      </div>
    </div>
  </section>

</main>

<script>
  // Función para toggle de contraseña
  function setupPasswordToggle(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const passwordToggle = document.getElementById(toggleId);

    passwordToggle.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

      // Cambiar el ícono SVG
      const eyeOpen = `<svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
      </svg>`;

      const eyeClosed = `<svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>`;

      this.innerHTML = type === 'password' ? eyeOpen : eyeClosed;
      this.title = type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña';
    });
  }

  // Configurar toggles
  setupPasswordToggle('password', 'password-toggle');
  setupPasswordToggle('password2', 'confirm-toggle');
</script>

<?php require_once '../components/footer.php'; ?>
</body>
</html>