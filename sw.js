const CACHE_VERSION = 'v1.1.0';
const STATIC_CACHE = `alphasupps-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `alphasupps-dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `alphasupps-images-${CACHE_VERSION}`;
const FONT_CACHE = `alphasupps-fonts-${CACHE_VERSION}`;

const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/views/index.php',
  '/css/critical.css',
  '/css/styles.css',
  '/css/reset.css',
  '/css/buttons.css',
  '/css/cards.css',
  '/css/header.css',
  '/css/footer.css',
  '/css/cart.css',
  '/css/supplement.css',
  '/css/collection.css',
  '/js/lazy-loading.js',
  '/js/cart.js',
  '/js/nav.js',
  '/js/modal.js',
  '/js/cookies.js',
  '/images/icon-192x192.png',
  '/images/icon-512x512.png',
  '/images/favicon_e0b94d.svg'
];

const NO_CACHE_PATTERNS = [
  /\/admin\//,
  /\/api\//,
  /\/controllers\//,
  /\/models\//,
  /\/config\./,
  /\/\.env/,
  /\?/,
  /login/,
  /register/,
  /cart/
];

/**
 * Determina si una URL debe ser cacheada.
 *
 * @param {URL} url - URL solicitada por el navegador.
 * @returns {boolean} true si debe cachearse, false si coincide con patrones bloqueados.
 */
function shouldCache(url) {
  const urlString = url.toString();
  return !NO_CACHE_PATTERNS.some(pattern => pattern.test(urlString));
}

/**
 * Elimina cachés antiguos que no correspondan a la versión actual.
 *
 * @returns {Promise<void>} Promesa que se resuelve tras borrar los cachés viejos.
 */
async function cleanOldCaches() {
  const cacheNames = await caches.keys();
  const validCaches = [STATIC_CACHE, DYNAMIC_CACHE];

  return Promise.all(
    cacheNames
      .filter(name => !validCaches.includes(name))
      .map(name => caches.delete(name))
  );
}

/**
 * Devuelve las duraciones máximas permitidas para cada tipo de caché.
 *
 * @returns {object} Duraciones en milisegundos para cada caché.
 */
function getCacheExpiration() {
  const oneWeek = 7 * 24 * 60 * 60 * 1000;
  const oneMonth = 30 * 24 * 60 * 60 * 1000;

  return {
    static: oneMonth,
    dynamic: oneWeek,
    images: oneMonth,
    fonts: oneMonth * 3
  };
}

/**
 * Verifica si un recurso almacenado en caché ha expirado.
 *
 * @param {string} cacheName - Nombre del caché.
 * @param {number} timestamp - Marca temporal cuando fue guardado.
 * @returns {boolean} true si expiró, false si sigue válido.
 */
function isCacheExpired(cacheName, timestamp) {
  const expiration = getCacheExpiration();
  const now = Date.now();

  if (cacheName.includes('static')) return now - timestamp > expiration.static;
  if (cacheName.includes('dynamic')) return now - timestamp > expiration.dynamic;
  if (cacheName.includes('images')) return now - timestamp > expiration.images;
  if (cacheName.includes('fonts')) return now - timestamp > expiration.fonts;

  return false;
}

self.addEventListener('install', event => {
  console.log('[SW] Installing Service Worker');
  event.waitUntil(
    (async () => {
      const cache = await caches.open(STATIC_CACHE);
      console.log('[SW] Caching static assets');
      await cache.addAll(STATIC_ASSETS);
      return self.skipWaiting();
    })()
  );
});

self.addEventListener('activate', event => {
  console.log('[SW] Activating Service Worker');
  event.waitUntil(
    (async () => {
      await cleanOldCaches();
      return self.clients.claim();
    })()
  );
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  if (url.origin !== location.origin) return;

  if (!shouldCache(url)) {
    return;
  }

  if (request.destination === 'image' ||
      url.pathname.startsWith('/images/') ||
      url.pathname.match(/\.(png|jpg|jpeg|gif|svg|webp)$/)) {

    event.respondWith(cacheFirstWithStaleWhileRevalidate(request, IMAGE_CACHE));
    return;
  }

  if (request.destination === 'font' ||
      url.pathname.startsWith('/fonts/') ||
      url.href.includes('fonts.googleapis.com') ||
      url.href.includes('fonts.gstatic.com')) {

    event.respondWith(cacheFirst(request, FONT_CACHE));
    return;
  }

  if (request.destination === 'style' ||
      request.destination === 'script' ||
      url.pathname.startsWith('/css/') ||
      url.pathname.startsWith('/js/')) {

    event.respondWith(cacheFirst(request, STATIC_CACHE));
    return;
  }

  if (request.destination === 'document' ||
      url.pathname.startsWith('/views/')) {

    event.respondWith(networkFirst(request, DYNAMIC_CACHE));
    return;
  }

  event.respondWith(staleWhileRevalidate(request, DYNAMIC_CACHE));
});

/**
 * Estrategia Cache First:
 * Devuelve versión cacheada si existe; si no, consulta la red y almacena el resultado.
 *
 * @param {Request} request - Solicitud a procesar.
 * @param {string} cacheName - Caché donde se guarda la respuesta.
 * @returns {Promise<Response>} Respuesta HTTP.
 */
async function cacheFirst(request, cacheName) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.log('[SW] Cache First failed:', error);
    return new Response('', { status: 404 });
  }
}

/**
 * Estrategia Cache First + Stale While Revalidate:
 * Devuelve caché si existe, pero actualiza en segundo plano.
 *
 * @param {Request} request - Solicitud a atender.
 * @param {string} cacheName - Caché en el que almacenar recursos.
 * @returns {Promise<Response>} Respuesta desde cache o red.
 */
async function cacheFirstWithStaleWhileRevalidate(request, cacheName) {
  const cachedResponse = await caches.match(request);

  if (cachedResponse) {
    fetch(request).then(networkResponse => {
      if (networkResponse.ok) {
        const cache = caches.open(cacheName);
        cache.then(cache => cache.put(request, networkResponse));
      }
    }).catch(() => {});
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.log('[SW] Stale While Revalidate failed:', error);
    return new Response('', { status: 404 });
  }
}

/**
 * Estrategia Network First:
 * Intenta obtener contenido desde la red; si falla, usa caché.
 *
 * @param {Request} request - Solicitud que se procesa.
 * @param {string} cacheName - Caché donde almacenar en éxito.
 * @returns {Promise<Response>} Respuesta desde la red o desde caché.
 */
async function networkFirst(request, cacheName) {
  try {
    const networkResponse = await fetch(request);

    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    console.log('[SW] Network First failed, trying cache:', error);

    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }

    if (request.destination === 'document') {
      return getOfflinePage();
    }

    return new Response('', { status: 404 });
  }
}

/**
 * Estrategia Stale While Revalidate:
 * Devuelve caché si existe, pero actualiza siempre desde la red en segundo plano.
 *
 * @param {Request} request - Solicitud procesada.
 * @param {string} cacheName - Caché donde guardar actualizaciones.
 * @returns {Promise<Response>} Respuesta HTTP.
 */
async function staleWhileRevalidate(request, cacheName) {
  const cachedResponse = await caches.match(request);

  const networkUpdate = fetch(request).then(networkResponse => {
    if (networkResponse.ok) {
      const cache = caches.open(cacheName);
      cache.then(cache => cache.put(request, networkResponse));
    }
    return networkResponse;
  }).catch(() => cachedResponse);

  return cachedResponse || networkUpdate;
}

/**
 * Devuelve una página HTML personalizada para mostrar cuando el usuario está offline.
 *
 * @returns {Response} Documento HTML básico para modo sin conexión.
 */
function getOfflinePage() {
  return new Response(`
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>AlphaSupps - Sin conexión</title>
      <style>
        body {
          font-family: 'Inter', sans-serif;
          text-align: center;
          padding: 50px 20px;
          background: #f9f9f9;
          color: #2a2a2a;
          margin: 0;
        }
        .offline-container {
          max-width: 500px;
          margin: 0 auto;
          background: white;
          border-radius: 12px;
          padding: 40px 30px;
          box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }
        h1 {
          color: #e0b94d;
          margin-bottom: 20px;
          font-family: 'Poppins', sans-serif;
          font-weight: 800;
        }
        p {
          margin: 15px 0;
          line-height: 1.6;
        }
        .retry-btn {
          background: linear-gradient(135deg, #e0b94d, #ffd700);
          color: #2a2a2a;
          border: none;
          padding: 14px 28px;
          border-radius: 8px;
          font-weight: 600;
          cursor: pointer;
          margin-top: 20px;
          transition: all 0.3s ease;
        }
        .retry-btn:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(224, 185, 77, 0.3);
        }
        .status {
          font-size: 14px;
          color: #666;
          margin-top: 20px;
        }
      </style>
    </head>
    <body>
      <div class="offline-container">
        <h1>🔄 Sin conexión</h1>
        <p>Lo sentimos, no podemos conectar con AlphaSupps en este momento.</p>
        <p>Esto podría deberse a problemas de conexión o mantenimiento del servidor.</p>
        <button class="retry-btn" onclick="window.location.reload()">Reintentar conexión</button>
        <div class="status">Última actualización: ${new Date().toLocaleString('es-ES')}</div>
      </div>
    </body>
    </html>
  `, {
    headers: { 'Content-Type': 'text/html' }
  });
}

self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});