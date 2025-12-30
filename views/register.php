<?php require_once '../controllers/register.php'; ?>
<!DOCTYPE html>
<html lang="es">

<?php require_once '../components/head.php'; ?>
<?php require_once '../components/cookies.php'; ?>

<body>
<?php require_once '../components/header.php'; ?>

<main class="home">

  <section class="hero">
    <h1>Únete a la Comunidad de Atletas Inteligentes</h1>
    <p class="section-subtitle">
      Crea tu cuenta para gestionar pedidos, suscripciones y packs personalizados con información transparente.
      No prometemos resultados ni porcentajes; decide con tu propio criterio o asesoramiento profesional.
    </p>

    <?php if ($error): ?>
      <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="login-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="login-card">
      <form method="POST" class="form login-form" action="<?php echo BASE_URL; ?>controllers/user.php">
      <input type="hidden" name="action" value="register">

      <div class="form-group">
        <label for="name" class="form-label">Nombre completo</label>
        <input
          type="text"
          name="name"
          id="name"
          class="form-input"
          placeholder="Tu nombre"
          required
        >
      </div>

      <div class="form-group">
        <label for="email" class="form-label">Correo electrónico</label>
        <input
          type="email"
          name="email"
          id="email"
          class="form-input"
          placeholder="ejemplo@correo.com"
          required
        >
      </div>

      <div class="form-group password-input-container">
        <label for="password" class="form-label">Contraseña</label>
        <input
          type="password"
          name="password"
          id="password"
          class="form-input"
          placeholder="••••••••"
          required
        >
        <button type="button" class="password-toggle" id="password-toggle" title="Mostrar contraseña">
          <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
          </svg>
        </button>
      </div>

      <div class="form-group password-input-container">
        <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
        <input
          type="password"
          name="confirmPassword"
          id="confirmPassword"
          class="form-input"
          placeholder="••••••••"
          required
        >
        <button type="button" class="password-toggle" id="confirm-toggle" title="Mostrar contraseña">
          <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
          </svg>
        </button>
      </div>

      <button type="submit" class="btn btn-primary btn-lg">Crear mi cuenta</button>
      </form>

      <p class="login-link">
        ¿Ya tienes cuenta?
        <a href="./login.php">Inicia sesión aquí</a>
      </p>
    </div>
  </section>

</main>

<?php require_once '../components/footer.php'; ?>

<script>
  // Reutilizar misma función de toggle que en login
  function setupPasswordToggle(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const passwordToggle = document.getElementById(toggleId);

    if (!passwordInput || !passwordToggle) return;

    passwordToggle.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

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
  setupPasswordToggle('confirmPassword', 'confirm-toggle');
</script>
</body>
</html>
