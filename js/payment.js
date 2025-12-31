function setMensaje(texto, tipo = "info") {
  const mensaje = document.getElementById("mensaje");
  if (!mensaje) return;
  // Ya no se muestra, solo se mantiene para compatibilidad con posibles llamadas
  mensaje.textContent = "";
}

/* Función de diagnóstico para Stripe */
function diagnosticarStripe() {
  console.log('🔍 Diagnóstico de Stripe:');

  // Verificar clave pública
  if (!window.stripe) {
    console.error('❌ window.stripe no está definido');
    return false;
  }
  console.log('✅ window.stripe está definido');

  // Verificar elementos del DOM
  const elementos = ['card-number-element', 'card-expiry-element', 'card-cvc-element'];
  for (const id of elementos) {
    const el = document.getElementById(id);
    if (!el) {
      console.error(`❌ Elemento ${id} no encontrado en el DOM`);
      return false;
    }
    console.log(`✅ Elemento ${id} encontrado`);
  }

  console.log('✅ Diagnóstico completado');
  return true;
}

function setBotonCargando(cargando) {
  const btn = document.getElementById("submit");
  if (!btn) return;

  if (cargando) {
    btn.disabled = true;
    btn.textContent = "Procesando...";
    btn.classList.add("loading");
  } else {
    btn.disabled = false;
    btn.textContent = "Pagar ahora";
    btn.classList.remove("loading");
  }
}

/* 1) Inicializar Stripe */
function inicializarStripe() {
  if (!window.stripe) {
    console.error('❌ Stripe no está disponible. Verifica la configuración.');
    return null;
  }

  const elements = window.stripe.elements();

  const style = {
    base: {
      color: "#fff",
      fontSize: "16px",
      fontFamily: "Montserrat, Inter, sans-serif",
      "::placeholder": { color: "rgba(255,255,255,0.55)" }
    },
    invalid: { color: "#ff6b6b" }
  };

  const cardNumber = elements.create("cardNumber", { style });
  const cardExpiry = elements.create("cardExpiry", { style });
  const cardCvc = elements.create("cardCvc", { style });

  // Verificar que los elementos del DOM existen antes de montar
  const numberEl = document.getElementById("card-number-element");
  const expiryEl = document.getElementById("card-expiry-element");
  const cvcEl = document.getElementById("card-cvc-element");

  if (!numberEl || !expiryEl || !cvcEl) {
    console.error('❌ Los elementos del DOM para Stripe no se encontraron');
    return null;
  }

  console.log('✅ Montando elementos de Stripe...');
  cardNumber.mount("#card-number-element");
  cardExpiry.mount("#card-expiry-element");
  cardCvc.mount("#card-cvc-element");

  console.log('✅ Elementos de Stripe montados correctamente');

  // Agregar indicadores visuales cuando los elementos estén listos
  setTimeout(() => {
    const numberEl = document.getElementById("card-number-element");
    const expiryEl = document.getElementById("card-expiry-element");
    const cvcEl = document.getElementById("card-cvc-element");

    if (numberEl && numberEl.querySelector('iframe')) {
      console.log('✅ Elemento de número de tarjeta listo');
    }
    if (expiryEl && expiryEl.querySelector('iframe')) {
      console.log('✅ Elemento de expiración listo');
    }
    if (cvcEl && cvcEl.querySelector('iframe')) {
      console.log('✅ Elemento de CVC listo');
    }
  }, 1000);

  return { stripe: window.stripe, cardNumber };
}

/* 2) Carrito desde cart.js */
function obtenerCarritoCompleto() {
  if (typeof getCart !== "function") return [];
  const cart = getCart();

  return Object.entries(cart).map(([key, item]) => {
    let name = item.name;
    if (item.type === 'custom_pack') {
      const subItems = item.items.map(i => i.name).join(', ');
      name = `${item.name} (${subItems})`;
    }
    return {
      nombre: name,
      qty: item.qty,
      price: item.price
    };
  });
}

function obtenerItemsParaPago() {
  if (typeof getCart !== "function") return [];
  const cart = getCart();

  return Object.entries(cart).map(([key, item]) => ({
    id: key,
    qty: item.qty,
    subscribe: item.subscribe || false,
    interval: item.interval || null,
    type: item.type || null,
    name: item.name || null,
    items: Array.isArray(item.items) ? item.items.map(sub => ({
      id: sub.id,
      qty: sub.qty || 1
    })) : null
  }));
}

/* 3) Totales para orders */
function prepararCamposPedido(carrito) {
  if (!carrito.length) return null;

  const productText = carrito.map(p => `${p.nombre} x${p.qty}`).join(", ");
  const quantityTotal = carrito.reduce((t, p) => t + p.qty, 0);
  const priceTotal = carrito.reduce((t, p) => t + p.qty * p.price, 0);

  return {
    product: productText,
    quantity: quantityTotal,
    price: priceTotal.toFixed(2)
  };
}

/* 4) Datos del formulario */
function obtenerDatosFormulario() {
  return {
    name: document.getElementById("nombre").value.trim(),
    email: document.getElementById("email").value.trim(),
    address: document.getElementById("direccion").value.trim(),
    postal_code: document.getElementById("codigoPostal").value.trim(),
    phone: document.getElementById("telefono").value.trim()
  };
}

/* 4b) Guardado local de datos */
const STORAGE_KEY = "alphasupps_checkout";

async function loadCheckoutData() {
  // Si está logueado, intentar cargar desde servidor
  if (window.isLoggedIn) {
    try {
      const res = await fetch("../api/checkout-data.php", { credentials: "include" });
      if (res.ok) {
        const data = await res.json();
        if (data?.data) {
          hydrateForm(data.data);
          const remember = document.getElementById("remember-data");
          if (remember) remember.checked = true;
          return;
        }
      }
    } catch (e) {
      console.warn("No se pudo cargar checkout data del servidor", e);
    }
  }

  // Fallback: localStorage
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    const data = JSON.parse(raw);
    hydrateForm(data);
    const remember = document.getElementById("remember-data");
    if (remember) remember.checked = true;
  } catch (e) {
    console.error("No se pudieron cargar los datos guardados", e);
  }
}

function hydrateForm(data = {}) {
  if (data.name) document.getElementById("nombre").value = data.name;
  if (data.email) document.getElementById("email").value = data.email;
  if (data.address) document.getElementById("direccion").value = data.address;
  if (data.postal_code) document.getElementById("codigoPostal").value = data.postal_code;
  if (data.phone) document.getElementById("telefono").value = data.phone;
}

function saveCheckoutData(data) {
  if (window.isLoggedIn) {
    // Guardar en servidor
    fetch("../api/checkout-data.php", {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    }).catch(() => {});
  }
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  } catch (e) {
    console.warn("No se pudieron guardar los datos de checkout", e);
  }
}

function clearCheckoutData() {
  localStorage.removeItem(STORAGE_KEY);
  if (window.isLoggedIn) {
    fetch("../api/checkout-data.php", {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({})
    }).catch(() => {});
  }
}

/* 5) Validación */
function validarFormulario(datosForm, carrito) {
  if (!carrito.length) {
    return { ok: false, msg: "Tu carrito está vacío." };
  }

  if (!datosForm.name || datosForm.name.length < 3) {
    return { ok: false, msg: "Introduce tu nombre completo." };
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(datosForm.email)) {
    return { ok: false, msg: "Introduce un correo electrónico válido." };
  }

  if (!datosForm.address || datosForm.address.length < 5) {
    return { ok: false, msg: "Introduce una dirección completa." };
  }

  if (!datosForm.postal_code || datosForm.postal_code.length < 3) {
    return { ok: false, msg: "Introduce un código postal válido." };
  }

  const digits = datosForm.phone.replace(/\D/g, "");
  if (digits.length < 7) {
    return { ok: false, msg: "Introduce un teléfono válido." };
  }

  return { ok: true };
}

/* 6) Backend: crear PaymentIntent o Subscription */
async function crearPago(totalData, items, customer) {
  const res = await fetch("../api/order.php", {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      action: "crear_pago",
      precio: totalData.price,
      items: items,
      customer
    })
  });

  return res.json();
}

/* 7) Confirmar pago Stripe */
async function confirmarPago(stripe, cardNumber, data, clientSecret) {
  return await stripe.confirmCardPayment(clientSecret, {
    payment_method: {
      card: cardNumber,
      billing_details: {
        name: data.name,
        email: data.email,
        phone: data.phone,
        address: {
          line1: data.address,
          postal_code: data.postal_code
        }
      }
    }
  });
}

/* 8) Guardar pedido en backend */
async function guardarPedido(payload, paymentId, items, subscriptionId = null, subscriptionInvoiceId = null) {
  const res = await fetch("../api/order.php", {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      action: "guardar_pedido",
      ...payload,
      payment_id: paymentId,
      items: items,
      subscription_id: subscriptionId,
      subscription_invoice_id: subscriptionInvoiceId
    })
  });

  return res.json();
}

function mostrarToastPedido(resumen) {
  try {
    const existing = document.querySelector(".toast-order");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.className = "toast-order";

    const safeId = resumen.id || "—";
    const safeTotal = resumen.total ? Number(resumen.total).toFixed(2) + " €" : "";
    const trackUrl = `../views/track.php?order_id=${encodeURIComponent(safeId)}&email=${encodeURIComponent(resumen.email || "")}`;

    toast.innerHTML = `
      <div class="toast-order__top">
        <div class="pulse-dot"></div>
        <div class="toast-copy">
          <p class="kicker">Pedido confirmado</p>
          <p class="title">#${safeId}</p>
          ${safeTotal ? `<p class="sub">${safeTotal}</p>` : ""}
        </div>
        <button class="toast-close" aria-label="Cerrar notificación">&times;</button>
      </div>
      <a class="toast-order__cta" href="${trackUrl}">¿Ver seguimiento ahora?</a>
    `;

    toast.querySelector(".toast-close").addEventListener("click", () => toast.remove());
    toast.addEventListener("mouseenter", () => clearTimeout(toast._timer));
    toast.addEventListener("mouseleave", () => {
      toast._timer = setTimeout(() => toast.remove(), 8000);
    });

    document.body.appendChild(toast);
    toast._timer = setTimeout(() => toast.remove(), 8000);
  } catch (e) {
    // fallback simple
    if (typeof showToast === "function") {
      showToast("Estamos preparando tu pedido #" + (resumen.id || ""), "success");
    }
  }
}

/* 9) Envío de formulario */
async function manejarEnvioFormulario(e, stripe, cardNumber) {
  e.preventDefault();
  setMensaje("Procesando pago...", "info");
  setBotonCargando(true);

  const carrito = obtenerCarritoCompleto();
  const totales = prepararCamposPedido(carrito);
  const itemsParaPago = obtenerItemsParaPago();
  const datosForm = obtenerDatosFormulario();

  const valid = validarFormulario(datosForm, carrito);
  if (!valid.ok) {
    setMensaje("❌ " + valid.msg, "error");
    setBotonCargando(false);
    mostrarModalError(valid.msg);
    return;
  }

  try {
    const stripeData = await crearPago(totales, itemsParaPago, datosForm);
    if (!stripeData.client_secret) {
      const msg = stripeData.error || "Error al crear el pago.";
      setMensaje("❌ " + msg, "error");
      setBotonCargando(false);
      mostrarModalError(msg);
      return;
    }
    const subscriptionId = stripeData.subscription_id || null;
    const subscriptionInvoiceId = stripeData.invoice_id || null;

    const result = await confirmarPago(
      stripe,
      cardNumber,
      datosForm,
      stripeData.client_secret
    );

    if (result.error) {
      setMensaje("❌ " + result.error.message, "error");
      setBotonCargando(false);
      mostrarModalError(result.error.message);
      return;
    }

    if (result.paymentIntent?.status === "succeeded" || result.paymentIntent?.status === "processing") {
      const payload = { ...datosForm, ...totales };
      const pedido = await guardarPedido(payload, result.paymentIntent.id, itemsParaPago, subscriptionId, subscriptionInvoiceId);

      if (!pedido.success) {
        setMensaje("❌ " + (pedido.error || "Error guardando el pedido."), "error");
        setBotonCargando(false);
        mostrarModalError(pedido.error);
        return;
      }

      // 📦 RESUMEN TIPO SHOPIFY
      const resumen = {
        id: pedido.order_id || pedido.order?.id || result.paymentIntent.id,
        name: datosForm.name,
        email: datosForm.email,
        address: datosForm.address,
        phone: datosForm.phone,
        total: totales.price,
        items: carrito,
        gift: pedido.gift ?? pedido.order?.gift ?? null
      };

      // Mostrar modal con resumen SIN REDIRECCIÓN
      mostrarModalGracias(resumen);
      mostrarToastPedido(resumen);

      setMensaje("", "info");

      // Limpiar carrito y datos si no se pide recordar
      const remember = document.getElementById("remember-data");
      if (!remember || !remember.checked) {
        clearCheckoutData();
      }
    }

  } catch (err) {
    setMensaje("❌ " + err.message, "error");
    mostrarModalError(err.message);

  } finally {
    setBotonCargando(false);
  }
}

/* Placeholder dinámico */
document.addEventListener("DOMContentLoaded", () => {
  const inputs = document.querySelectorAll("input[placeholder]");

  inputs.forEach(input => {
    const original = input.getAttribute("placeholder");

    input.addEventListener("input", () => {
      if (input.value.trim() !== "") {
        input.removeAttribute("placeholder");
      } else {
        input.setAttribute("placeholder", original);
      }
    });

    input.addEventListener("focus", () => {
      input.removeAttribute("placeholder");
    });

    input.addEventListener("blur", () => {
      if (input.value.trim() === "") {
        input.setAttribute("placeholder", original);
      }
    });
  });
});

/* 10) DOM READY */
document.addEventListener("DOMContentLoaded", () => {
  console.log('🚀 Inicializando sistema de pagos...');
  loadCheckoutData();

  // Ejecutar diagnóstico
  setTimeout(() => {
    if (!diagnosticarStripe()) {
      setMensaje('Error: Problemas con la configuración de pagos. Revisa la consola.', 'error');
      return;
    }
  }, 500);

  const stripeData = inicializarStripe();

  if (!stripeData) {
    console.error('❌ No se pudieron inicializar los elementos de Stripe');
    setMensaje('Error: No se pudieron cargar los elementos de pago. Verifica la configuración.', 'error');
    return;
  }

  const { stripe, cardNumber } = stripeData;
  const form = document.getElementById("payment-form");

  if (form) {
    form.addEventListener("submit", (e) =>
      manejarEnvioFormulario(e, stripe, cardNumber)
    );
    const remember = document.getElementById("remember-data");
    form.addEventListener("input", () => {
      if (remember && remember.checked) {
        saveCheckoutData(obtenerDatosFormulario());
      }
    });
    if (remember) {
      remember.addEventListener("change", () => {
        if (remember.checked) {
          saveCheckoutData(obtenerDatosFormulario());
        } else {
          clearCheckoutData();
        }
      });
    }
  }

  console.log('✅ Sistema de pagos inicializado correctamente');
});
