<?php
require_once __DIR__ . '/../models/db.php';

$email = $_GET['email'] ?? '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<p style='color:red; text-align:center; font-family:Arial;'>Correo inválido.</p>";
    exit;
}

$conn = connectToDatabase();

$stmt = $conn->prepare("DELETE FROM newsletter_subscribers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $mensaje = "Tu suscripción se ha cancelado correctamente. Esperamos verte pronto 💪";
} else {
    $mensaje = "No encontramos tu correo o ya habías cancelado tu suscripción.";
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cancelar suscripción | AlphaSupps</title>
  <style>
    body {
      background-color: #0f0f0f;
      color: #f1f1f1;
      font-family: Arial, Helvetica, sans-serif;
      text-align: center;
      padding: 80px 20px;
    }
    .box {
      background-color: #111;
      border: 1px solid #222;
      border-radius: 12px;
      padding: 40px;
      max-width: 500px;
      margin: auto;
    }
    h1 {
      color: #e0b94d;
    }
    a {
      color: #e0b94d;
      text-decoration: none;
      font-weight: bold;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>AlphaSupps 💪</h1>
    <p><?= htmlspecialchars($mensaje) ?></p>
    <p><a href="https://alphasupps.alwaysdata.net/views">Volver a la web</a></p>
  </div>
</body>
</html>
