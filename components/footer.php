<!-- ===========================
      FOOTER
=========================== -->
<footer class="main-footer">

  <div class="footer-container">

    <div class="footer-block footer-logo">
      <a href="../views/index.php" class="footer-brand">
        <svg xmlns="http://www.w3.org/2000/svg" class="footer-icon" viewBox="0 0 100 100">
          <g fill="none" stroke="<?= $accent ?>" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="50" cy="50" r="46"/>
            <path d="M50 18 L72 75 L28 75 Z"/>
            <path d="M47 32 C49 38, 53 42, 51 47 C49 52, 43 56, 46 61 L56 72"/>
          </g>
        </svg>
        <span>Alpha<span class="gold">Supps</span></span>
      </a>

      <p class="footer-text">
        AlphaSupps es una tienda construida con una idea clara: ofrecer suplementos de calidad,
        packs pensados para mejorar tus entrenamientos y adaptados a tus ritmos y expectativas.
      </p>
    </div>

    <div class="footer-block">
      <h3>Explorar</h3>
      <ul>
        <li><a href="../views/index.php">Inicio</a></li>
        <li><a href="../views/packs.php">Packs</a></li>
        <li><a href="../views/supplements.php">Suplementos</a></li>
        <li><a href="../views/alphabox.php">AlphaBox</a></li>
        <li><a href="../views/story.php">Nuestra historia</a></li>
        <li><a href="../views/blog.php">Blog</a></li>
        <li><a href="../views/index.php#colleciones">Colecciones</a></li>
      </ul>
    </div>

    <div class="footer-block">
      <h3>Soporte</h3>
      <ul>
        <li><a href="../views/contact.php">Contacto</a></li>
        <li><a href="../views/faq.php">Preguntas frecuentes</a></li>
        <li><a href="../views/envios.php">Envíos y devoluciones</a></li>
        <li><a href="../views/privacy-policy.php">Política de privacidad</a></li>
        <li><a href="../views/cookies.php">Política de cookies</a></li>
        <li><a href="../views/legal-advise.php">Aviso legal</a></li>
        <li><a href="../views/terms.php">Términos y condiciones</a></li>
        <li><a href="../views/desistimiento.php">Derecho de desistimiento</a></li>
      </ul>
    </div>

    <div class="footer-block">
      <h3>Comunidad</h3>
      <ul>
        <li><a href="../views/register.php">Únete</a></li>
        <li><a href="../views/login.php">Acceder</a></li>
        <li><a href="../views/blog.php">Artículos</a></li>
        <li><a href="../views/story.php">Historia</a></li>
      </ul>
    </div>

    <div class="footer-block">
      <h3>Redes</h3>

      <div class="footer-social">
        <a href="https://instagram.com" target="_blank">📸 Instagram</a>
        <a href="https://tiktok.com" target="_blank">🎵 TikTok</a>
        <a href="mailto:<?= $settings['contact_email'] ?? 'soporte@alphasupps.com' ?>">📧 Email</a>
      </div>

      <p class="footer-text small">
        Recibe novedades, lanzamientos y descuentos exclusivos.
      </p>

      <a href="#" class="footer-btn" id="openNewsletter">Unirme al boletín</a>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© <?= date("Y") ?> AlphaSupps · Construido con disciplina y visión</p>
  </div>

</footer>


<!-- ===========================
      MODAL NEWSLETTER
=========================== -->
<div id="newsletterModal" class="alpha-modal">
  <div class="alpha-modal-content alpha-animate">

    <span class="alpha-modal-close" id="closeNewsletter">✕</span>

    <h2 class="alpha-title" style="color: var(--alpha-gold); text-align:center;">
      Únete al boletín
    </h2>

    <p class="alpha-text" style="text-align:center; margin-bottom: 15px;">
      Forma parte de la lista VIP y recibe descuentos, lanzamientos y contenido exclusivo.
    </p>

    <form id="newsletterForm" class="alpha-form">
      <input type="hidden" name="action" value="newsletter">

      <input type="email"
             name="email"
             class="alpha-input"
             placeholder="Tu email"
             required>

      <button type="submit" class="alpha-btn">Unirme</button>
    </form>

    <div id="newsletter-msg" class="alpha-message"></div>

  </div>
</div>


<!-- ===========================
      ANIMACIÓN + SCRIPT
=========================== -->
<style>
/* Animación suave estilo Alpha */
.alpha-animate {
  opacity: 0;
  transform: scale(0.92);
  transition: opacity 0.25s ease-out, transform 0.25s ease-out;
}

.alpha-modal.show .alpha-animate {
  opacity: 1;
  transform: scale(1);
}
.alpha-modal {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100vh;
  display: none;
  justify-content: center;
  align-items: center;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
}

.alpha-modal-content {
  background: #111;
  padding: 25px;
  border-radius: 12px;
  width: 90%;
  max-width: 420px;
}
:root {
    --alpha-bg: #0d0d0d;
    --alpha-card: #1a1a1a;
    --alpha-gold: #e6c068;
    --alpha-text: #ffffff;
    --alpha-danger: #ff4d4d;
    --alpha-success: #32d483;
    --alpha-font: 'Poppins', sans-serif;
}

/* Modal Overlay */
.alpha-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    justify-content: center;
    align-items: center;
    z-index: 2000;
}

/* Modal Box */
.alpha-modal-content {
    background: #1a1a1a;
    padding: 35px;
    width: 90%;
    max-width: 420px;
    border-radius: 16px;
    border: 1px solid #2a2a2a;
    box-shadow: 0 0 25px rgba(0,0,0,0.5);
    position: relative;
}

/* Close Button */
.alpha-modal-close {
    position: absolute;
    right: 18px;
    top: 12px;
    font-size: 28px;
    cursor: pointer;
    color: var(--alpha-gold);
    transition: 0.2s ease;
}
.alpha-modal-close:hover {
    color: #fff;
}

/* Inputs */
.alpha-input {
    width: 100%;
    padding: 14px;
    margin-bottom: 14px;
    border-radius: 10px;
    border: 1px solid #333;
    background: #111;
    color: var(--alpha-text);
    font-size: 1rem;
    transition: .25s;
}
.alpha-input:focus {
    border-color: var(--alpha-gold);
    outline: none;
}

/* Botón */
.alpha-btn {
    width: 100%;
    background: var(--alpha-gold);
    color: #000;
    padding: 14px;
    border-radius: 10px;
    border: none;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: .25s;
}
.alpha-btn:hover {
    background: #f0d890;
}

/* Mensajes */
.alpha-message {
    display: none;
    margin-top: 15px;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
}
.alpha-success {
    background: rgba(50, 212, 131, 0.2);
    color: var(--alpha-success);
    border: 1px solid var(--alpha-success);
}
.alpha-error {
    background: rgba(255, 77, 77, 0.2);
    color: var(--alpha-danger);
    border: 1px solid var(--alpha-danger);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {

  const modal = document.getElementById("newsletterModal");
  const modalContent = modal.querySelector(".alpha-animate");
  const openBtn = document.getElementById("openNewsletter");
  const closeBtn = document.getElementById("closeNewsletter");
  const form = document.getElementById("newsletterForm");
  const msgBox = document.getElementById("newsletter-msg");

  // Abrir modal con animación
  openBtn.addEventListener("click", (e) => {
    e.preventDefault();
    modal.style.display = "flex";
    setTimeout(() => modal.classList.add("show"), 10);
  });

  // Cerrar modal
  const closeModal = () => {
    modal.classList.remove("show");
    msgBox.textContent = "";
    msgBox.className = "alpha-message";

    setTimeout(() => {
      modal.style.display = "none";
    }, 200);
  };

  closeBtn.addEventListener("click", closeModal);

  window.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  // Envío del formulario
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    msgBox.style.display = "block";
    msgBox.textContent = "Procesando…";
    msgBox.className = "alpha-message";

    const resp = await fetch("../controllers/user.php", {
      method: "POST",
      body: formData
    });

    const text = await resp.text();

    if (text.includes("Gracias")) {
      msgBox.classList.add("alpha-success");
      form.reset();
    } else {
      msgBox.classList.add("alpha-error");
    }

    msgBox.textContent = text;
  });

});
</script>