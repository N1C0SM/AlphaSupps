<?php
 require_once '../models/order.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Por favor introduce un correo válido.";

    } else {


$conexion = new mysqli(
    "mysql-alphasupps.alwaysdata.net",
    "432149",
    "Dxq954pA",
    "alphasupps_db",
    3306
);


        if ($conexion->connect_errno) {
            $mensaje = "Error interno. Inténtalo más tarde.";

        } else {

            $stmt = $conexion->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {

                $token = bin2hex(random_bytes(32));
                $expiracion = date("Y-m-d H:i:s", time() + 3600);

                $stmt2 = $conexion->prepare(
                    "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?"
                );
                $stmt2->bind_param("sss", $token, $expiracion, $email);
                $stmt2->execute();

                $enlace = "https://alphasupps.alwaysdata.net/views/reset_password.php?token=$token";

                // Usar sistema modular unificado de emails
                require_once '../modules/email-system.php';
                sendPasswordResetEmail($email, $enlace);
            }
            $mensaje = "Si el correo existe, te enviaremos un enlace para restablecer tu contraseña.";
            $stmt->close();
            if (isset($stmt2)) $stmt2->close();
            $conexion->close();
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
    <h1>Recupera Tu Acceso</h1>
    <p class="section-subtitle">
      Te enviaremos un enlace seguro para restablecer tu contraseña. Revisa tu bandeja de entrada y carpeta de spam.
    </p>

    <?php if ($mensaje) : ?>
      <div class="login-success">
        <?= htmlspecialchars($mensaje) ?>
      </div>
    <?php endif; ?>

    <div class="login-card">
      <form method="POST" class="login-form" action="">
        <label for="email">Correo electrónico</label>
        <input type="email" name="email" id="email" placeholder="tuemail@ejemplo.com" required>

        <button type="submit" class="btn btn-primary btn-lg">📧 Enviar Enlace de Recuperación</button>
      </form>

      <div class="login-link">
        <a href="login.php">← Volver al inicio de sesión</a>
      </div>
    </div>
  </section>

  <section class="why-us">
    <h2>¿Problemas con el acceso?</h2>
    <div class="why-grid">
      <div class="why-item">
        <div class="icon">🔒</div>
        <h3>Enlace seguro por email</h3>
        <p>Recibirás un enlace único y seguro directamente en tu correo electrónico para restablecer tu contraseña.</p>
      </div>
      <div class="why-item">
        <div class="icon">⚡</div>
        <h3>Recuperación en minutos (según correo)</h3>
        <p>El proceso es sencillo. La mayoría de veces el enlace llega en pocos minutos, pero puede variar según tu proveedor de email.</p>
      </div>
      <div class="why-item">
        <div class="icon">🛡️</div>
        <h3>Proceso protegido y privado</h3>
        <p>Tu información se trata con cifrado en tránsito y buenas prácticas de seguridad, sin prometer resultados imposibles.</p>
      </div>
    </div>
  </section>

</main>

<?php require_once '../components/footer.php'; ?>
</body>
</html>
