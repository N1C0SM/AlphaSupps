<?php

$contactEmail = $settings['contact_email'] ?? 'soporte@alphasupps.com';
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once "../components/head.php"; ?>
<body>
<?php require_once "../components/header.php"; ?>
<main class="legal-page page page-contact">

<h1>¿Cuántos Mg de Zinc Tomas Diariamente?</h1>
<p class="intro">Cuéntanos tu duda sobre suplementación y te respondemos con información transparente y, cuando sea posible, referencias públicas.
No damos asesoramiento médico ni prometemos resultados; buscamos que tomes decisiones informadas.</p>

<div class="contact-wrapper">

  <!-- FORMULARIO -->
  <form class="contact-form" id="contactForm">
    <input type="hidden" name="action" value="contact">

    <label>Nombre</label>
    <input type="text" name="name" placeholder="Tu nombre" required>

    <label>Email</label>
    <input type="email" name="email" placeholder="Tu email" required>

    <label>Mensaje</label>
    <textarea name="message" rows="5" placeholder="Escribe aquí tu mensaje..." required></textarea>

    <button type="submit" class="btn-primary">Enviar mensaje</button>
  </form>

  <!-- INFORMACIÓN -->
  <div class="contact-info">
    <h3>Expertos que Conocen Tu Rutina</h3>
    <p>📧 Email: <a href="mailto:<?= $contactEmail ?>"><?= $contactEmail ?></a></p>
    <p>🕒 Suelen ser menos de 24h para dudas técnicas y unas horas para soporte básico (depende de la cola).</p>
    <p>👥 Equipo de soporte con experiencia en nutrición deportiva y entrenamiento de fuerza.</p>
    <p>📍 Madrid/Barcelona, con experiencia en gimnasios locales.</p>

    <div class="testimonials" style="margin-top: 1.5rem; padding: 1rem; background: var(--bg-panel); border-radius: var(--radius);">
      <p style="font-style: italic; color: var(--text-muted); margin-bottom: 0.5rem;">"Gracias por enviarme referencias y explicarlo sin prometer resultados. Ahora sé qué revisar con mi nutricionista."</p>
      <p style="font-size: 0.9rem; color: var(--accent);">- Ana G., powerlifter, Barcelona</p>
    </div>
  </div>

</div>

</main>
<?php require_once "../components/footer.php"; ?>
<script>
document.getElementById("contactForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const response = await fetch("../controllers/user.php", {
      method: "POST",
      body: formData
  });

  const text = await response.text();
  alert(text); // puedes cambiarlo por modal limpio
});
</script>
</body>
</html>
