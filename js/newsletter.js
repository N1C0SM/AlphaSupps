/**
 * ==========================================================
 * 🌐 script.js — Manejo general de formularios del sitio
 * Incluye:
 *  - Formulario de registro (#registroForm)
 *  - Formulario de newsletter (#newsletterForm)
 * ==========================================================
 */

/* ----------------------------------------------------------
 * 🔧 Función genérica: mostrar mensajes de resultado
 * ---------------------------------------------------------- */
function mostrarMensaje(elemento, texto, tipo = 'info') {
  if (!elemento) return;
  elemento.textContent = texto;
  elemento.style.color =
    tipo === 'success' ? 'green' :
    tipo === 'error' ? 'red' :
    '#333';
}

/* ----------------------------------------------------------
 * ✉️ Enviar formulario genérico con Fetch
 * ---------------------------------------------------------- */
async function enviarFormulario(form, mensaje, url = null) {
  const formData = new FormData(form);
  const destino = url || form.action || window.location.href;

  try {
    const res = await fetch(destino, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const text = await res.text();

    const tipo = text.toLowerCase().includes('exitosa') ? 'success' : 'error';
    mostrarMensaje(mensaje, text, tipo);

    form.reset();
  } catch (err) {
    mostrarMensaje(mensaje, '⚠️ Error al enviar el formulario.', 'error');
    console.error('❌ Error al enviar formulario:', err);
  }
}

/* ----------------------------------------------------------
 * 🧩 Inicializar formulario de registro
 * ---------------------------------------------------------- */
function inicializarFormularioRegistro() {
  const form = document.getElementById('registroForm');
  const mensaje = document.getElementById('mensaje');

  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    enviarFormulario(form, mensaje);
  });
}

/* ----------------------------------------------------------
 * 🧩 Inicializar formulario de newsletter
 * NOTA: El newsletter se maneja con JavaScript inline en footer.php
 * ---------------------------------------------------------- */
function inicializarNewsletter() {
  // Newsletter se maneja en footer.php para evitar conflictos
  return;
}

/* ----------------------------------------------------------
 * 🚀 Inicialización global
 * ---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
  inicializarFormularioRegistro();
  inicializarNewsletter();
});