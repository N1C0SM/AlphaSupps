<!DOCTYPE html>
<html lang="es">
<?php require_once '../components/head.php'; ?>
<body>
  <?php require_once '../components/header.php';?>
  <main class="legal-page">
    <section class="legal-container">
      <h1>Aviso Legal</h1>
      <p>
        El presente Aviso Legal regula el uso del sitio web
        <strong><?= getenv('USER_WEBSITE') ?></strong> (en adelante, el “Sitio Web”),
        titularidad de <strong><?= getenv('EMISOR_NAME') ?></strong> (en adelante, “AlphaSupps” o “el Titular”).
      </p>
      <h2>1. Datos del titular</h2>
      <p>
        <strong>Titular:</strong> <?= getenv('EMISOR_NAME') ?><br>
        <strong>NIF/CIF:</strong> <?= getenv('EMISOR_NIF') ?><br>
        <strong>Domicilio social:</strong> <?= getenv('EMISOR_ADDRESS') ?><br>
        <strong>Teléfono:</strong> <?= getenv('EMISOR_PHONE') ?><br>
        <strong>Email de contacto:</strong>
        <a href="mailto:<?= getenv('EMISOR_EMAIL') ?>"><?= getenv('EMISOR_EMAIL') ?></a><br>
        <strong>Sitio web:</strong>
        <a href="<?= getenv('USER_WEBSITE') ?>"><?= getenv('USER_WEBSITE') ?></a>
      </p>
      <h2>2. Objeto del sitio web</h2>
      <p>
        A través del Sitio Web, AlphaSupps ofrece información y comercialización
        de productos de suplementación deportiva, nutrición y bienestar.
      </p>
      <p>
        La información contenida en el Sitio Web tiene carácter general y no constituye
        asesoramiento médico, sanitario, nutricional ni jurídico profesional.
      </p>
      <h2>3. Condiciones de uso</h2>
      <p>
        El acceso y uso del Sitio Web atribuye la condición de usuario e implica la
        aceptación plena de lo dispuesto en este Aviso Legal. Si el usuario no está
        de acuerdo, deberá abstenerse de utilizar el Sitio Web.
      </p>
      <p>
        El usuario se compromete a utilizar el Sitio Web conforme a la ley, la buena fe
        y el orden público, y a no provocar daños en sistemas del Titular o de terceros.
      </p>
      <h2>4. Asesoramiento médico y responsabilidad sobre el uso de productos</h2>
      <p>
        Los productos ofrecidos están destinados a adultos sanos. El usuario debe consultar
        con un profesional sanitario antes de consumir suplementos si padece enfermedades,
        toma medicación, está embarazada o en lactancia.
      </p>
      <p>
        AlphaSupps no se responsabiliza por el uso inadecuado de los productos ni por la
        falta de consulta médica previa.
      </p>
      <h2>5. Limitación de responsabilidad</h2>
      <p>
        AlphaSupps no garantiza la disponibilidad permanente del Sitio Web ni se hace responsable
        de daños derivados de interrupciones, fallos técnicos o virus informáticos.
      </p>
      <h2>6. Enlaces</h2>
      <p>
        El Sitio Web puede incluir enlaces a sitios de terceros. AlphaSupps no es responsable
        del contenido ni de la gestión de dichos sitios.
      </p>
      <h2>7. Propiedad intelectual e industrial</h2>
      <p>
        Todos los contenidos del Sitio Web son propiedad del Titular o cuentan con autorización
        para su uso. Su reproducción o distribución sin permiso está prohibida.
      </p>
      <h2>8. Protección de datos</h2>
      <p>
        El tratamiento de datos personales se rige por la
        <a href="./privacy-policy.php">Política de Privacidad</a>
        y la <a href="./cookies.php">Política de Cookies</a>.
      </p>
      <h2>9. Modificaciones</h2>
      <p>
        El Titular podrá modificar este Aviso Legal en cualquier momento para adaptarlo
        a cambios normativos o técnicos.
      </p>
      <h2>10. Legislación y jurisdicción</h2>
      <p>
        Este Aviso Legal se rige por la legislación española. Salvo lo dispuesto por
        la normativa de consumo, las partes se someten a los Juzgados y Tribunales de Madrid.
      </p>
      <h2>11. Ausencia de asesoramiento profesional</h2>
      <p>
        La información del Sitio Web no constituye asesoramiento profesional. El usuario
        debe consultar con especialistas antes de tomar decisiones basadas en ella.
      </p>
    </section>
  </main>
  <?php require_once '../components/footer.php';?>
</body>
</html>