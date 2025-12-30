const isLoggedIn = window.isLoggedIn || false;

/* ==========================================
   STORAGE
========================================== */
function getStorage() {
  return isLoggedIn ? localStorage : sessionStorage;
}

function getCart() {
  try {
    return JSON.parse(getStorage().getItem("cart")) || {};
  } catch {
    return {};
  }
}

function saveCart(cart) {
  getStorage().setItem("cart", JSON.stringify(cart));

  if (isLoggedIn) {
    fetch("../api/cart.php?action=save", {
      method: "POST",
      body: JSON.stringify(cart),
    }).catch(() => {});
  }
}

/* ==========================================
   ANIMACIÓN ICONO DEL CARRITO (HEADER)
========================================== */
function animateCartIcon() {
  const btn = document.querySelector(".cart-btn");
  if (!btn) return;

  btn.classList.remove("bump");
  void btn.offsetWidth;
  btn.classList.add("bump");
}

/* ==========================================
   TOAST
========================================== */
function showToast(msg, type = 'success') {
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      const toast = document.createElement("div");
      toast.textContent = msg;
      toast.className = `toast toast-${type}`;
      document.body.appendChild(toast);

      setTimeout(() => toast.remove(), 3000);
    });
  });
}

/* ==========================================
   RENDER DEL CARRITO (LISTA)
========================================== */
function renderCart() {
  const container = document.getElementById("cartItems");
  if (!container) return;

  const cart = getCart();
  container.innerHTML = "";
  let total = 0;

  Object.entries(cart).forEach(([id, item]) => {
    const subtotal = item.qty * item.price;
    total += subtotal;

    const card = document.createElement("div");
    card.className = "cart-item";

    const intervalLabel = item.interval ? `cada ${item.interval} meses` : 'activa';
    const isSubscription = item.subscribe
      ? `<div class="sub-tag">Suscripción: ${intervalLabel} (-10%)</div>`
      : "";

    card.innerHTML = `
      <div class="item-thumb">
        <img src="${item.icon || item.image}" alt="${item.name}">
      </div>

      <div class="item-info">
        <div class="item-name">${item.name}</div>
        ${isSubscription}
        <div class="item-price">${item.price.toFixed(2)} €</div>
      </div>

      <div class="item-controls">
        <div class="qty-box">
          <div class="qty-number">${item.qty}</div>
          <div class="qty-arrows">
            <button class="arrow up" onclick='changeQty("${id}", 1)'>
              <img src="../images/icons/arrow-up.svg" width="16">
            </button>
            <button class="arrow down" onclick='changeQty("${id}", -1)'>
              <img src="../images/icons/arrow-down.svg" width="16">
            </button>
          </div>
        </div>

        <div class="item-subtotal">${subtotal.toFixed(2)} €</div>

        <button class="remove-btn" onclick='removeItem("${id}")'>
          <img src="../images/icons/trash.svg" width="16">
        </button>
      </div>
    `;

    container.appendChild(card);
  });

  const totalEl = document.getElementById("total");
  if (totalEl) totalEl.innerText = total.toFixed(2) + " €";

  updateCartSummary();
}

/* ==========================================
   ACTUALIZAR MENSAJE "Tienes X productos"
========================================== */
function updateCartSummary() {
  const cart = getCart();
  const count = Object.values(cart).reduce((acc, item) => acc + item.qty, 0);

  // Actualizar el contador del header
  const counter = document.getElementById("cart-count");
  if (counter) counter.textContent = count;

  // Actualizar también .cart-summary si existe (para la página del carrito)
  const summary = document.querySelector(".cart-summary span");
  if (summary) summary.textContent = count;
}

/* ==========================================
   CAMBIAR CANTIDAD
========================================== */
function changeQty(id, delta) {
  const cart = getCart();
  if (!cart[id]) return;

  cart[id].qty = Math.max(1, cart[id].qty + delta);
  saveCart(cart);

  renderCart();
  updateCartCount();
}

/* ==========================================
   ELIMINAR ITEM
========================================== */
function removeItem(id) {
  const cart = getCart();
  delete cart[id];
  saveCart(cart);

  renderCart();
  updateCartCount();
}

/* ==========================================
   ADD TO CART NORMAL
========================================== */
function addToCart(product, showNotification = true) {
  const cart = getCart();

  if (cart[product.id]) {
    cart[product.id].qty++;
  } else {
    cart[product.id] = {
      name: product.name,
      qty: 1,
      price: product.price,
      icon: product.icon,
      subscribe: product.subscribe || false,
      interval: product.interval || null
    };
  }

  saveCart(cart);
  updateCartCount();
  animateCartIcon();

  // Mostrar solo el mini preview (sin toast) si showNotification es true
  if (showNotification) {
    showMiniCartPreview(product);
  }
}

/* ==========================================
   CONTADOR DEL HEADER
========================================== */
function updateCartCount() {
  updateCartSummary();
}

/* ==========================================
   SUSCRIPCIÓN
========================================== */
function addToCartWithSubscription(product) {
  // Buscar el toggle de suscripción en el DOM
  const toggle = document.getElementById("subscribe-toggle");
  const intervalSelect = document.getElementById("subscribe-interval");

  if (toggle && toggle.checked && intervalSelect) {
    const interval = parseInt(intervalSelect.value);
    const discountedPrice = product.price * 0.9;

    const subscriptionProduct = {
      ...product,
      id: product.id + "_sub_" + interval,
      price: discountedPrice,
      subscribe: true,
      interval: interval
    };

    addToCart(subscriptionProduct);
  } else {
    addToCart(product);
  }
}

/* ==========================================
   COMPRA INSTANTÁNEA CON SUSCRIPCIÓN
========================================== */
function instantBuyWithSubscription(product) {
  const toggle = document.getElementById("subscribe-toggle");
  const intervalSelect = document.getElementById("subscribe-interval");

  if (toggle && toggle.checked && intervalSelect) {
    const interval = parseInt(intervalSelect.value);
    const discountedPrice = product.price * 0.9;

    const subscriptionProduct = {
      ...product,
      id: product.id + "_sub_" + interval,
      price: discountedPrice,
      subscribe: true,
      interval: interval
    };

    addToCart(subscriptionProduct, false);
  } else {
    addToCart(product, false);
  }

  // Redirigir al carrito para proceder al checkout (sin esperar)
  window.location.assign("../views/cart.php");
}

/* ==========================================
   INIT
========================================== */
document.addEventListener("DOMContentLoaded", () => {
  renderCart();
  updateCartCount();
});

/* ==========================================
   MINI PREVIEW DEL CARRITO
========================================== */
let miniCartTimeout;

function getMiniCartContainer() {
  let container = document.getElementById("mini-cart-preview");
  if (container) return container;

  container = document.createElement("div");
  container.id = "mini-cart-preview";
  container.className = "mini-cart-preview";
  container.innerHTML = `
    <div class="mini-cart-header">
      <span class="mini-cart-title">Añadido al carrito</span>
      <button class="mini-cart-close" aria-label="Cerrar">×</button>
    </div>
    <div class="mini-cart-body">
      <img class="mini-cart-thumb" src="" alt="Producto añadido">
      <div class="mini-cart-info">
        <div class="mini-cart-name"></div>
        <div class="mini-cart-meta">
          <span class="mini-cart-count"></span>
          <span class="mini-cart-total"></span>
        </div>
      </div>
    </div>
    <div class="mini-cart-actions">
      <button class="mini-cart-view">Ver carrito</button>
    </div>
  `;

  document.body.appendChild(container);

  container.querySelector(".mini-cart-close").addEventListener("click", hideMiniCartPreview);
  container.querySelector(".mini-cart-view").addEventListener("click", () => {
    window.location.href = "../views/cart.php";
  });

  return container;
}

function showMiniCartPreview(product) {
  const cart = getCart();
  const container = getMiniCartContainer();
  if (!container) return;

  const thumb = container.querySelector(".mini-cart-thumb");
  const name = container.querySelector(".mini-cart-name");
  const countEl = container.querySelector(".mini-cart-count");
  const totalEl = container.querySelector(".mini-cart-total");

  const fallbackImg = product.icon || product.image || "/images/default.png";
  if (thumb) thumb.src = fallbackImg;
  if (name) name.textContent = product.name || "Producto añadido";

  const count = Object.values(cart).reduce((acc, item) => acc + item.qty, 0);
  const total = Object.values(cart).reduce((acc, item) => acc + item.qty * item.price, 0);

  if (countEl) countEl.textContent = `${count} ${count === 1 ? "producto" : "productos"}`;
  if (totalEl) totalEl.textContent = `€${total.toFixed(2)}`;

  container.classList.add("visible");

  clearTimeout(miniCartTimeout);
  miniCartTimeout = setTimeout(() => {
    hideMiniCartPreview();
  }, 3200);
}

function hideMiniCartPreview() {
  const container = document.getElementById("mini-cart-preview");
  if (container) {
    container.classList.remove("visible");
  }
}
