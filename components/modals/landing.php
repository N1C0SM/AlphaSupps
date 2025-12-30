<style>
:root {
    --alpha-bg: #0d0d0d;
    --alpha-card: #1a1a1a;
    --alpha-gold: #f0c75e;
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
    color: var(--alpha-text);
    font-family: var(--alpha-font);
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
<div id="alpha-prelaunch-modal" class="alpha-modal">
  <div class="alpha-modal-content">
    <span class="alpha-modal-close">&times;</span>

    <h2 class="alpha-title" style="color: var(--alpha-gold); text-align:center; margin-bottom:6px;">
      Acceso anticipado Alpha
    </h2>

    <p style="text-align:center; font-size:0.9rem; margin-bottom:12px; color:#ddd;">
      Estamos en <strong>prelanzamiento</strong>.
      Apúntate para ser de los primeros en entrar cuando abramos la tienda.
    </p>

    <p style="text-align:center; font-size:0.85rem; margin-bottom:18px; color:#bbb;">
      Verás antes que nadie los <strong>packs por objetivo</strong>
      (masa, definición, principiantes y Pack Alpha ⚡)
      y recibirás las <strong>condiciones y avisos de lanzamiento</strong>.
    </p>

    <form id="prelaunch-form">
      <input type="hidden" name="action" value="prelaunch">

      <input
        type="text"
        name="name"
        placeholder="Tu nombre"
        class="alpha-input"
        required
      >

      <input
        type="email"
        name="email"
        placeholder="Tu email"
        class="alpha-input"
        required
      >

      <button type="submit" class="alpha-btn">
        Quiero acceso anticipado
      </button>
    </form>

    <p style="text-align:center; font-size:0.75rem; margin-top:10px; color:#aaa;">
      No compras nada ahora.
      No envío spam. Solo avisos del lanzamiento y novedades importantes.
    </p>

    <div id="prelaunch-msg" class="alpha-message"></div>
  </div>
</div>
