function ensureModalStructure() {
  if (!document.getElementById("modal-gracias")) {
    const modal = document.createElement("div");
    modal.id = "modal-gracias";
    modal.className = "modal premium-modal";
    modal.innerHTML = `
      <div class="premium-modal-content order-modal">
        <h2 class="premium-title">¡Gracias por tu compra! 🎉</h2>
        <p class="premium-text">Tu pedido se ha completado correctamente.</p>
        <div class="order-summary">
          <div class="order-summary-row">
            <span class="label">ID del pedido</span>
            <span class="value" id="order-id">-</span>
          </div>
          <div class="order-summary-row">
            <span class="label">Nombre</span>
            <span class="value" id="order-name">-</span>
          </div>
          <div class="order-summary-row">
            <span class="label">Email</span>
            <span class="value" id="order-email">-</span>
          </div>
          <div class="order-summary-row">
            <span class="label">Dirección</span>
            <span class="value" id="order-address">-</span>
          </div>
          <div class="order-summary-row">
            <span class="label">Teléfono</span>
            <span class="value" id="order-phone">-</span>
          </div>
          <hr class="order-divider">
          <div class="order-items">
            <div class="order-items-header">
              <span>Producto</span>
              <span>Cant.</span>
              <span>Precio</span>
            </div>
            <div id="order-items-body"></div>
          </div>
	          <div class="order-summary-row total">
	            <span class="label">Total pagado</span>
	            <span class="value" id="order-total">0.00 €</span>
	          </div>
	          <div class="order-summary-row" id="order-gift-row" style="display:none;">
	            <span class="label">Regalo</span>
	            <span class="value" id="order-gift">-</span>
	          </div>
	          <p class="order-legal">
	            Este resumen no sustituye a la factura. Recibirás un correo con todos los detalles de tu pedido.
	          </p>
	        </div>
	        <button class="premium-button" onclick="cerrarModalYVaciarCarrito()">Aceptar</button>
      </div>
    `;
    document.body.appendChild(modal);
  }

  if (!document.getElementById("modal-error")) {
    const modal = document.createElement("div");
    modal.id = "modal-error";
    modal.className = "modal premium-modal";
    modal.innerHTML = `
      <div class="premium-modal-content error">
        <h2 class="premium-title">❌ Ups… hubo un error</h2>
        <p id="modal-error-texto" class="premium-text">Error desconocido.</p>
        <button class="premium-button error-btn" onclick="cerrarModalErrorYVaciarCarrito()">Cerrar</button>
      </div>
    `;
    document.body.appendChild(modal);
  }
}

document.addEventListener("DOMContentLoaded", ensureModalStructure);

function showModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.style.display = "flex";
  modal.classList.add("visible");
}

function hideModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.remove("visible");
  modal.style.display = "none";
}

function rellenarResumenPedido(resumen) {
  if (!resumen) return;

  document.getElementById("order-id").textContent = resumen.id || "-";
  document.getElementById("order-name").textContent = resumen.name || "-";
  document.getElementById("order-email").textContent = resumen.email || "-";
  document.getElementById("order-address").textContent = resumen.address || "-";
  document.getElementById("order-phone").textContent = resumen.phone || "-";
  document.getElementById("order-total").textContent = (resumen.total || "0.00") + " €";

  const giftRow = document.getElementById("order-gift-row");
  const giftEl = document.getElementById("order-gift");
  if (giftRow) giftRow.style.display = "none";
  if (giftEl) giftEl.textContent = "-";

  if (resumen.gift) {
    const giftLabelMap = {
      prozis_bar: "Barritas (Prozis)"
    };
    const giftText = giftLabelMap[resumen.gift] || String(resumen.gift);
    if (giftEl) giftEl.textContent = giftText;
    if (giftRow) giftRow.style.display = "flex";
  }

  const body = document.getElementById("order-items-body");
  body.innerHTML = "";

  if (Array.isArray(resumen.items)) {
    resumen.items.forEach(item => {
      const row = document.createElement("div");
      row.className = "order-item-row";
      row.innerHTML = `
        <span>${item.nombre}</span>
        <span>x${item.qty}</span>
        <span>${item.price.toFixed(2)} €</span>
      `;
      body.appendChild(row);
    });
  }
}

function mostrarModalGracias(resumen) {
  if (resumen) rellenarResumenPedido(resumen);
  showModal("modal-gracias");
}

function mostrarModalError(mensaje) {
  document.getElementById("modal-error-texto").innerText = mensaje || "Error desconocido.";
  showModal("modal-error");
}

function vaciarCarritoFront() {
  const storage = window.isLoggedIn ? localStorage : sessionStorage;
  storage.setItem("cart", JSON.stringify({}));

  if (typeof renderCart === "function") renderCart();
  if (typeof updateCartCount === "function") updateCartCount();

  if (window.isLoggedIn) {
    fetch("../api/cart.php?action=save", {
      method: "POST",
      body: JSON.stringify({})
    }).catch(() => {});
  }

  if (typeof showToast === "function") {
    showToast("Carrito vacío");
  }
}

// Cerrar modales + vaciar carrito
function cerrarModalYVaciarCarrito() {
  hideModal("modal-gracias");
  vaciarCarritoFront();
  // Redirección opcional:
  // window.location.href = "./index.php";
}

function cerrarModalErrorYVaciarCarrito() {
  hideModal("modal-error");
  vaciarCarritoFront();
}
