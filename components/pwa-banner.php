

<style>
#pwa-install-banner {
  position: fixed;
  bottom: 20px;
  left: 20px;
  right: 20px;
  background: linear-gradient(135deg, #e0b94d, #ffd700);
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(224, 185, 77, 0.3);
  z-index: 10000;
  animation: slideUp 0.5s ease-out;
  max-width: 400px;
  margin: 0 auto;
  display: none;
}

@keyframes slideUp {
  from { transform: translateY(100px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.banner-content {
  display: flex;
  align-items: center;
  padding: 16px;
  gap: 12px;
}

.banner-icon {
  font-size: 24px;
  background: rgba(255,255,255,0.2);
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.banner-text {
  flex: 1;
  color: #2a2a2a;
}

.banner-text strong {
  display: block;
  font-size: 16px;
  margin-bottom: 4px;
  font-weight: 600;
}

.banner-text span {
  font-size: 14px;
  opacity: 0.8;
  line-height: 1.3;
}

.banner-buttons {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.install-button {
  background: #2a2a2a;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.install-button:hover {
  background: #1a1a1a;
  transform: translateY(-1px);
}

.install-button:active {
  transform: translateY(0);
}

.dismiss-options {
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: flex-end;
}

.dismiss-button {
  background: transparent;
  color: #2a2a2a;
  border: 1px solid rgba(42,42,42,0.3);
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
}

.dismiss-button:hover {
  background: rgba(42,42,42,0.1);
}

.dismiss-button-small {
  background: transparent;
  color: #666;
  border: none;
  padding: 2px 8px;
  border-radius: 3px;
  cursor: pointer;
  font-size: 11px;
  transition: all 0.2s;
  text-decoration: underline;
}

.dismiss-button-small:hover {
  background: rgba(42,42,42,0.05);
  color: #444;
}

@media (max-width: 480px) {
  #pwa-install-banner {
    left: 10px;
    right: 10px;
    bottom: 10px;
  }

  .banner-content {
    padding: 12px;
    gap: 8px;
  }

  .banner-icon {
    width: 40px;
    height: 40px;
    font-size: 20px;
  }

  .banner-text strong {
    font-size: 14px;
  }

  .banner-text span {
    font-size: 12px;
  }

  .banner-buttons {
    flex-direction: column;
    gap: 6px;
  }

  .install-button,
  .dismiss-button {
    padding: 6px 12px;
    font-size: 13px;
  }
}

@supports (-webkit-touch-callout: none) {
  #pwa-install-banner {
    bottom: 30px;
  }
}
</style>

<div id="pwa-install-banner">
  <div class="banner-content">
    <div class="banner-icon">💪</div>
    <div class="banner-text">
      <strong>AlphaSupps como app</strong>
      <span>Instala un acceso directo y guarda partes del sitio para usar con conexión limitada</span>
    </div>
    <div class="banner-buttons">
      <button id="pwa-install-btn" class="install-button">Agregar</button>
      <div class="dismiss-options">
        <button id="pwa-dismiss-session" class="dismiss-button">Ahora no</button>
        <button id="pwa-dismiss-never" class="dismiss-button-small">Nunca más</button>
      </div>
    </div>
  </div>
</div>

<script>
let deferredPrompt;
const installBanner = document.getElementById('pwa-install-banner');

function showPwaBanner() {
  if (localStorage.getItem('pwa-banner-never') === 'true') {
    return;
  }

  if (sessionStorage.getItem('pwa-banner-dismissed') === 'true') {
    return;
  }

  installBanner.style.display = 'block';
}

window.addEventListener('beforeinstallprompt', function(e) {
  console.log('[PWA] beforeinstallprompt event fired');

  e.preventDefault();

  deferredPrompt = e;

  setTimeout(() => {
    showPwaBanner();
  }, 10000);
});

window.addEventListener('appinstalled', function(e) {
  console.log('[PWA] App was installed successfully');

  installBanner.style.display = 'none';

  deferredPrompt = null;
});

document.getElementById('pwa-install-btn').addEventListener('click', function() {
  installBanner.style.display = 'none';

  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(function(choiceResult) {
      if (choiceResult.outcome === 'accepted') {
        console.log('[PWA] Usuario instaló la app');
      }
      deferredPrompt = null;
    });
  }
});

document.getElementById('pwa-dismiss-session').addEventListener('click', function() {
  installBanner.style.display = 'none';
  sessionStorage.setItem('pwa-banner-dismissed', 'true');
});

document.getElementById('pwa-dismiss-never').addEventListener('click', function() {
  installBanner.style.display = 'none';
  localStorage.setItem('pwa-banner-never', 'true');
});

window.resetPwaBanner = function() {
  sessionStorage.removeItem('pwa-banner-dismissed');
  localStorage.removeItem('pwa-banner-never');
  deferredPrompt = null;
  console.log('[PWA] Banner reset - solo para testing');
};
</script>
