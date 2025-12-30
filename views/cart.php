<!doctype html>
<html lang="es">

<?php require_once '../components/head.php'; ?>

<body>
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripePublicKey = "<?= STRIPE_PUBLIC_KEY ?? 'pk_test_' ?>";
    console.log('Stripe Public Key:', stripePublicKey);

    if (!stripePublicKey || stripePublicKey.includes('...')) {
        console.error('❌ Stripe Public Key no está configurada correctamente');
        alert('Error: Las claves de Stripe no están configuradas. Contacta al administrador.');
    } else {
        const stripe = Stripe(stripePublicKey);
        console.log('✅ Stripe inicializado correctamente:', stripe);
        window.stripe = stripe;
    }
</script>
<script src="../js/payment.js?v=4"></script>
<?php require_once '../components/header.php'; ?>
<main class="checkout-container">
<section class="cart-list">
  <h2>Tu carrito</h2>
  <p class="cart-summary">Tienes <span id="cart-count">0</span> productos en tu carrito.</p>
  <div id="cartItems"></div>
</section>
<section class="checkout-panel">
  <h3>Detalles del pedido</h3>
  <form id="payment-form" autocomplete="off" spellcheck="false">
    <div class="field">
      <label for="nombre">Nombre completo</label>
      <input type="text" id="nombre" placeholder="Nombre completo"  autocomplete="name">
    </div>
    <div class="field">
      <label for="email">Correo electrónico</label>
      <input type="email" id="email" placeholder="Correo electrónico"  autocomplete="email">
    </div>
    <div class="field">
      <label for="direccion">Dirección</label>
      <input type="text" id="direccion" placeholder="Dirección completa"  autocomplete="address-line1">
    </div>
    <div class="field">
      <label for="codigoPostal">Código postal</label>
      <input type="text" id="codigoPostal" placeholder="Código postal" >
    </div>
    <div class="field">
      <label for="telefono">Teléfono</label>
      <input type="tel" id="telefono" placeholder="Teléfono" >
    </div>
    <div class="field remember-row">
      <label>
        <input type="checkbox" id="remember-data">
        Guardar estos datos para la próxima compra
      </label>
    </div>
    <div class="field">
      <label for="card-number-element">Número de tarjeta</label>
      <div id="card-number-element"></div>
    </div>
    <div class="field-group">
      <div class="field">
        <label for="card-expiry-element">Expiración</label>
        <div id="card-expiry-element"></div>
      </div>
      <div class="field">
        <label for="card-cvc-element">CVV</label>
        <div id="card-cvc-element"></div>
      </div>
    </div>
    <div class="totals">
      <div class="line"><span>Subtotal</span><span id="subtotal">0.00 €</span></div>
      <div class="line"><span>Gastos de envío</span><span id="tax">0.00 €</span></div>
      <div class="line total"><span>Total</span><span id="total">0.00 €</span></div>
    </div>
    <input type="hidden" id="payment_id" name="payment_id">
    <button class="checkout-btn" id="submit" type="submit">Pagar ahora</button>
  </form>
</section>
</main>
<?php require_once '../components/modals/thanks.php'; ?>
<?php require_once '../components/modals/error.php'; ?>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
