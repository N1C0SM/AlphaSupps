<!DOCTYPE html>
<html lang="es">
<?php require_once '../components/head.php'; ?>
<body>
  <?php require_once '../components/header.php'; ?>

  <main class="legal-page">
    <section class="legal-container">

      <h1>Términos y Condiciones</h1>
      <p>
        El presente documento regula el acceso y uso del sitio web
        <strong><?= getenv('USER_WEBSITE') ?></strong>, titularidad de <strong>AlphaSupps</strong>
        (en adelante, “el Titular”). Al navegar por este sitio, el usuario acepta estas condiciones.
      </p>

      <h2>1. Objeto</h2>
      <p>
        AlphaSupps ofrece información, contenido y venta online de suplementos nutricionales,
        así como packs y otros productos relacionados con el bienestar y el rendimiento deportivo.
      </p>

      <h2>2. Condiciones de uso</h2>
      <p>El usuario se compromete a:</p>
      <ul>
        <li>Realizar un uso responsable, lícito y de buena fe del sitio y sus servicios.</li>
        <li>No llevar a cabo actividades que puedan dañar la web, su funcionamiento o a otros usuarios.</li>
        <li>Proporcionar datos veraces en los formularios que lo requieran (registro, compra, newsletter, etc.).</li>
      </ul>

      <h2>3. Proceso de compra</h2>
      <ul>
        <li>Los precios se muestran en euros e incluyen impuestos, salvo indicación expresa.</li>
        <li>Antes de finalizar la compra, el usuario puede revisar y confirmar los datos del pedido.</li>
        <li>Los pagos se realizan mediante plataformas seguras (ej.: Stripe u otras pasarelas autorizadas).</li>
        <li>Una vez completado el pedido, se enviará una confirmación y la factura electrónica al email indicado.</li>
      </ul>

      <h2>4. Envíos y devoluciones</h2>
      <ul>
        <li>El tiempo estimado de procesamiento es de 24–48 horas laborables.</li>
        <li>El usuario dispone de un derecho de desistimiento de 14 días naturales desde la recepción del producto.</li>
        <li>Los artículos deben devolverse sin usar y en su embalaje original para ser aceptados.</li>
        <li>Por motivos de higiene, ciertos productos pueden no ser retornables una vez abiertos.</li>
      </ul>

      <h2>5. Responsabilidad</h2>
      <p>
        AlphaSupps no se hace responsable de interrupciones del servicio causadas por terceros,
        mantenimiento, fallos técnicos, ataques informáticos o cualquier circunstancia fuera del control del Titular.
      </p>

      <h2>6. Propiedad intelectual</h2>
      <p>
        El contenido del sitio (textos, imágenes, logotipos, gráficos, diseño y estructura) es propiedad del Titular
        o se utiliza bajo licencia. Queda prohibida su reproducción o uso no autorizado.
      </p>

      <h2>7. Protección de datos</h2>
      <p>
        Los datos personales se tratan conforme a nuestra
        <a href="/views/privacidad.php">Política de Privacidad</a>.
      </p>

      <h2>8. Ley aplicable y jurisdicción</h2>
      <p>
        Este documento se rige por la legislación española. En caso de conflicto, serán competentes los juzgados
        correspondientes al domicilio del consumidor.
      </p>

      <h2>9. Contacto</h2>
      <p>
        Para cualquier consulta, puedes escribir a:
        <a href="mailto:<?= getenv('MAIL_HOST') ?>"><?= getenv('MAIL_HOST') ?></a>
      </p>

    </section>
  </main>

  <?php require_once '../components/footer.php'; ?>
</body>
</html>