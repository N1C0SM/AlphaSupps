<div id="modal-gracias" class="modal premium-modal">
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
</div>


