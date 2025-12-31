if ('serviceWorker' in navigator) {
  const swUrl = '/js/sw.js';

  function attachUpdateListener(registration) {
    registration.addEventListener('updatefound', function() {
      const newWorker = registration.installing;
      if (newWorker) {
        newWorker.addEventListener('statechange', function() {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            if (confirm('Nueva versión disponible. ¿Actualizar ahora?')) {
              newWorker.postMessage({ type: 'SKIP_WAITING' });
              window.location.reload();
            }
          }
        });
      }
    });
  }

  window.addEventListener('load', function() {
    navigator.serviceWorker
      .register(swUrl, { scope: '/' })
      .catch(function(error) {
        console.warn('[SW] Root scope not allowed, retrying with default scope', error);
        return navigator.serviceWorker.register(swUrl);
      })
      .then(function(registration) {
        console.log('[SW] Registered successfully:', registration.scope);
        attachUpdateListener(registration);
      })
      .catch(function(error) {
        console.log('[SW] Registration failed:', error);
      });
  });

  window.addEventListener('load', function() {
    if (window.matchMedia('(display-mode: standalone)').matches) {
      document.body.classList.add('pwa-mode');
    }
  });
}
