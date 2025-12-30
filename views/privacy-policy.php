
<!DOCTYPE html>
<html lang="en">
  <?php require_once '../components/head.php'; ?>
  <?php require_once '../components/cookies.php'; ?>
<body>
<?php require_once '../components/header.php';?>
  <main class="legal-page">
  <section class="legal-container">
    <h1>Política de Privacidad</h1>
    <p>En <strong>AlphaSupps</strong> nos comprometemos a proteger tu privacidad y garantizar un uso responsable de tus datos personales, conforme al Reglamento (UE) 2016/679 (RGPD) y la Ley Orgánica 3/2018 (LOPDGDD).</p>

    <h2>1. Responsable del tratamiento</h2>
    <p><strong>Titular:</strong> <?= getenv('USER_FULL_NAME') ?><br>
    <strong>NIF/CIF:</strong> <?= getenv('USER_DNI') ?><br>
    <strong>Domicilio:</strong> <?= getenv('Calle Falsa 123, 28000 Madrid, España') ?><br>
    <strong>Email:</strong> <a href="mailto:<?= getenv('MAIL_USERNAME') ?>"><?= getenv('MAIL_USERNAME') ?></a><br>
    <strong>Sitio web:</strong> <a href="<?= getenv('USER_WEBSITE') ?>"><?= getenv('USER_WEBSITE') ?></a></p>

    <h2>2. Datos que recopilamos</h2>
    <ul>
      <li>Identificación: nombre, apellidos, dirección, teléfono, email.</li>
      <li>Pago y envío: datos necesarios para procesar pedidos.</li>
      <li>Navegación: IP, identificadores, cookies/almacenamiento local (ver <a href="/views/cookies.php">Política de Cookies</a>).</li>
      <li>Comunicación: datos facilitados en formularios o newsletter.</li>
    </ul>

    <h2>3. Finalidades</h2>
    <ul>
      <li>Gestionar compras, pagos, envíos y atención al cliente.</li>
      <li>Comunicaciones relativas a tu pedido o cuenta.</li>
      <li>Envío de promociones previo consentimiento.</li>
      <li>Analítica web para mejorar la experiencia (previo consentimiento).</li>
    </ul>

    <h2>4. Legitimación</h2>
    <p>Ejecución de un contrato (compra) y consentimiento del interesado (newsletter, analítica).</p>

    <h2>5. Conservación</h2>
    <p>Durante el tiempo necesario para cumplir finalidades y obligaciones legales (fiscales/contables).</p>

    <h2>6. Destinatarios</h2>
    <p>Proveedores necesarios para la prestación del servicio (por ejemplo, transporte o pasarelas de pago como Stripe) con sus correspondientes garantías.</p>

    <h2>7. Derechos</h2>
    <p>Acceso, rectificación, supresión, oposición, limitación y portabilidad en <a href="mailto:<?= getenv('MAIL_HOST')?>">i<?= getenv('MAIL_HOST')?></a>. Reclamo ante la AEPD si lo consideras oportuno.</p>

    <h2>8. Seguridad</h2>
    <p>Aplicamos medidas técnicas y organizativas adecuadas para proteger tus datos.</p>

    <h2>9. Cookies</h2>
    <p>Consulta la <a href="/views/cookies.php">Política de Cookies</a> para conocer el uso y configuración.</p>

    <h2>10. Cambios</h2>
    <p>Podremos actualizar esta política para adaptarla a cambios normativos o del servicio.</p>
  </section>
  </main>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
