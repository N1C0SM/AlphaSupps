<!doctype html>
<html lang="es">
  <?php require_once '../components/head.php'; ?>
  <style>
    body {
      background: #0f0f0f;
      color: #f1f1f1;
      font-family: 'Montserrat', sans-serif;
      margin: 0; padding: 2rem;
    }
    h1 {
      text-align: center;
      color: #e0b94d;
      margin-bottom: 1rem;
    }
    form {
      background: #1a1a1a;
      max-width: 600px;
      margin: 2rem auto;
      padding: 2rem;
      border-radius: 12px;
      border: 1px solid #333;
    }
    label {
      display: block;
      font-weight: 600;
      color: #e0b94d;
      margin-top: 1rem;
      margin-bottom: 0.3rem;
    }
    input, textarea {
      width: 100%;
      background: #111;
      color: #f1f1f1;
      border: 1px solid #333;
      border-radius: 8px;
      padding: 0.6rem;
      font-family: inherit;
    }
    input:focus, textarea:focus {
      outline: none;
      border-color: #e0b94d;
    }
    button {
      background: #e0b94d;
      color: #111;
      border: none;
      border-radius: 8px;
      padding: 0.8rem 1.5rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 1.5rem;
      transition: 0.3s;
      width: 100%;
    }
    button:hover {
      background: #f1cf6b;
    }
    p.note {
      text-align: center;
      color: #bbb;
      font-size: 0.9rem;
      margin-top: 1rem;
    }
  </style>
<body>
<?php require_once '../components/header.php'; ?>

  <main class="legal-page">
    <h1>Derecho de Desistimiento</h1>
  <p style="text-align:center; max-width:600px; margin:auto;">
    Completa este formulario para generar tu documento de desistimiento (sin enviar datos a ningún servidor).
  </p>
  <form id="desist-form">
    <label>Nombre y apellidos *</label>
    <input type="text" name="nombre" required>

    <label>Email *</label>
    <input type="email" name="email" required>

    <label>Número de pedido *</label>
    <input type="text" name="pedido" required>

    <label>Fecha de recepción *</label>
    <input type="date" name="fecha_recepcion" required>

    <label>Productos a devolver *</label>
    <textarea name="productos" rows="3" required></textarea>

    <label>Motivo (opcional)</label>
    <textarea name="motivo" rows="3"></textarea>

    <button type="submit">Generar PDF</button>

    <p class="note">Al generar el documento aceptas nuestra <a href="/views/privacidad.php" style="color:#e0b94d;">Política de Privacidad</a>.</p>
  </form>
  </main>
  <?php require_once '../components/footer.php'; ?>
  <script>
    document.getElementById('desist-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = e.target;
      const data = {
        nombre: form.nombre.value.trim(),
        email: form.email.value.trim(),
        pedido: form.pedido.value.trim(),
        fecha: form.fecha_recepcion.value.trim(),
        productos: form.productos.value.trim(),
        motivo: form.motivo.value.trim()
      };

      const html = `
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <title>Desistimiento ${data.pedido}</title>
        <style>
          body { font-family: Helvetica, sans-serif; color: #111; padding: 40px; }
          img { display:block; margin:auto; width:60px; }
          h1 { color: #e0b94d; text-align:center; margin-bottom:10px; }
          p, td, th { font-size: 14px; line-height:1.6; }
          table { width:100%; border-collapse: collapse; margin-top: 20px; }
          th, td { border: 1px solid #999; padding: 8px; text-align: left; vertical-align: top; }
          th { background: #f4f4f4; width: 35%; }
          .firma { margin-top: 50px; display: flex; justify-content: space-between; }
          .firma div { width: 45%; border-top: 1px solid #000; text-align: center; padding-top: 5px; }
          footer { margin-top: 40px; text-align:center; font-size: 12px; color: #666; }
        </style>
      </head>
      <body>
        <img src="https://alphasupps.alwaysdata.net/images/isotype-rounded.png" alt="AlphaSupps Logo">
        <h1>Formulario de Desistimiento</h1>
        <p>Generado automáticamente desde <strong>AlphaSupps</strong>.</p>

        <table>
          <tr><th>Nombre y apellidos</th><td>${data.nombre}</td></tr>
          <tr><th>Email</th><td>${data.email}</td></tr>
          <tr><th>Número de pedido</th><td>${data.pedido}</td></tr>
          <tr><th>Fecha de recepción</th><td>${data.fecha}</td></tr>
          <tr><th>Productos a devolver</th><td>${data.productos.replace(/\n/g, '<br>')}</td></tr>
          <tr><th>Motivo</th><td>${data.motivo ? data.motivo.replace(/\n/g, '<br>') : '—'}</td></tr>
        </table>

        <p style="margin-top:25px;">
          Declaro que deseo ejercer mi derecho de desistimiento del contrato de compraventa de los productos indicados,
          conforme al Texto Refundido de la Ley General para la Defensa de los Consumidores y Usuarios (TRLGDCU).
        </p>

        <div class="firma">
          <div>Lugar y fecha</div>
          <div>Firma del consumidor/a</div>
        </div>

        <footer>© ${new Date().getFullYear()} AlphaSupps — Documento generado automáticamente</footer>
        <script>window.onload = () => window.print();<\/script>
      </body>
      </html>`;

      const ventana = window.open('', '_blank');
      ventana.document.write(html);
      ventana.document.close();
    });
  </script>
</body>
</html>
