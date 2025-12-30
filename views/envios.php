<!DOCTYPE html>
<html lang="en">
  <?php require_once '../components/head.php'; ?>
  <body>
  <?php require_once '../components/header.php'; ?>
  <main class="legal-page">
  <section class="legal-container">
    <h1>Envíos y Plazos</h1>
    <p>Queremos que la información sea clara. Aquí tienes nuestras condiciones de envío, plazos y seguimiento.</p>
    <h2>Zonas de envío</h2>
    <ul>
      <li><strong>España peninsular</strong>: disponible.</li>
      <li><strong>Baleares</strong>: disponible (puede variar el plazo).</li>
      <li><strong>Canarias, Ceuta y Melilla</strong>: consultar antes de comprar.</li>
    </ul>
    <h2>Plazos de preparación y entrega</h2>
    <ul>
      <li><strong>Preparación</strong>: 24–48 horas laborables.</li>
      <li><strong>Entrega</strong>: 24–72 horas laborables desde la salida del almacén (según destino y operador).</li>
      <li>En campañas, festivos o incidencias logísticas, los plazos pueden ampliarse ligeramente.</li>
    </ul>
    <h2>Costes de envío</h2>
    <ul>
      <li><strong>España peninsular</strong>: desde X,XX € (gratis a partir de XX,XX € de compra).</li>
      <li><strong>Baleares</strong>: desde X,XX €.</li>
    </ul>
    <p><em>El coste exacto aparece calculado en el checkout antes de pagar.</em></p>
    <h2>Seguimiento</h2>
    <p>Cuando tu pedido salga del almacén, recibirás un email con el número de seguimiento de la agencia de transporte. Si no lo recibes, revisa tu carpeta de spam o escríbenos.</p>
    <h2>Incidencias de envío</h2>
    <ul>
      <li>Si tu pedido llega dañado, anótalo en el albarán del repartidor y contáctanos en <a href="mailto:info@alphasupps.es">info@alphasupps.es</a> en <strong>24–48 h</strong>.</li>
      <li>Si faltan productos o hay un error, avísanos y lo resolveremos con prioridad.</li>
    </ul>
    <h2>Direcciones incorrectas o ausencias</h2>
    <p>Si la dirección facilitada es incorrecta o hay ausencias reiteradas, podrían aplicarse costes de reexpedición a cargo del cliente.</p>

    <h2>Devoluciones por desistimiento</h2>
    <p>Consulta el <a href="/views/desistimiento.php">Derecho de Desistimiento</a> para conocer el proceso y condiciones.</p>
    <h2>Contacto</h2>
    <p><a href="mailto:<?= getenv('MAIL_USERNAME') ?>"><?= getenv('MAIL_USERNAME') ?></a></p>
  </section>
</main>
<?php require_once '../components/footer.php'; ?>
</body>
</html>

