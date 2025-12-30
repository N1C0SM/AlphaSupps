<!DOCTYPE html>
<html lang="en">
<?php require_once '../components/head.php'; ?>
<body>
  <?php require_once '../components/header.php'; ?>
    <main class="legal-page">
      <section class="legal-container">
        <h1>Política de Cookies</h1>
        <p>En <strong>AlphaSupps</strong> solo usamos cookies técnicas necesarias para que el sitio funcione (sesión y procesos de pago). No cargamos cookies de analítica ni marketing, por lo que no se requiere consentimiento ni banner.</p>

        <h2>1. ¿Qué es una cookie?</h2>
        <p>Es un pequeño archivo que se guarda en tu dispositivo al navegar. Puede ser propia (gestionada por AlphaSupps) o de terceros (p. ej., pasarela de pago).</p>

        <h2>2. Tipos de cookies que usamos</h2>
        <ul>
          <li><strong>Necesarias</strong>: imprescindibles para el funcionamiento del sitio y la seguridad (no requieren consentimiento).</li>
        </ul>

        <h2>3. Cookies concretas que usamos</h2>
        <ul>
          <li><strong>PHPSESSID</strong> (propia): mantiene tu sesión iniciada y carritos. Expira al cerrar el navegador.</li>
          <li><strong>Stripe</strong> (tercera, solo en pagos): puede establecer identificadores temporales para procesar pagos de forma segura.</li>
          <li><strong>localStorage</strong> (propio): guardamos datos de carrito/preferencias; no es cookie pero es almacenamiento técnico en tu dispositivo.</li>
        </ul>

        <h2>4. Cómo gestionar o eliminar cookies técnicas</h2>
        <p>
          Puedes borrar o bloquear cookies técnicas desde tu navegador:
          <a href="https://support.google.com/chrome/answer/95647?hl=es" target="_blank" rel="noopener">Chrome</a>,
          <a href="https://support.mozilla.org/es/kb/Borrar%20cookies" target="_blank" rel="noopener">Firefox</a>,
          <a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Safari</a>,
          <a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-las-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">Edge</a>.
        </p>

        <h2>6. Contacto</h2>
        <p>Para más información, escribe a <a href="mailto:<?= getenv('MAIL_HOST') ?>"><?= getenv('MAIL_USERNAME') ?></a>.</p>
      </section>
    </main>
    <?php require_once '../components/footer.php'; ?>
</body>
</html>





