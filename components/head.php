<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$user = $_SESSION['user'] ?? null;
$admin = $user && (($user['role'] ?? '') === 'admin');
require_once '../config/seo.php';
$path = $_SERVER['SCRIPT_FILENAME'];
$currentPage = basename($path, '.php');

require_once '../controllers/settings.php';
require_once '../models/user.php';
require_once '../controllers/config.php';
require_once '../models/analytics.php';
require_once '../models/post.php';
require_once '../models/db.php';
$hasSubscriptions = false;
if ($user && isset($user['id'])) {
  $connHead = connectToDatabase();
  if ($connHead) {
    $uidHead = (int)$user['id'];
    $resHead = $connHead->query("SELECT 1 FROM subscriptions WHERE user_id = {$uidHead} LIMIT 1");
    if ($resHead && $resHead->num_rows > 0) {
      $hasSubscriptions = true;
    }
  }
}
$pageData = $pages[$currentPage] ?? null;

$title          = $pageData['title']        ?? 'AlphaSupps';
$description    = $pageData['description']  ?? 'Tienda de suplementos AlphaSupps: calidad y ciencia para tu rendimiento.';
$canonical      = $pageData['canonical']    ?? '';
$uniqueFavicon  = $viewData['uniqueFavicon'] ?? 'favicon.svg';

?>

<head lang="es">

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <meta name="theme-color" content="<?= $accent ?>" />

  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($description) ?>" />
  <meta name="author" content="AlphaSupps" />
  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
  <meta name="googlebot" content="index, follow" />
  <meta name="bingbot" content="index, follow" />

  <!-- Canonical URL -->
  <?php if ($canonical): ?>
      <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
  <?php endif; ?>

  <!-- Performance optimizations -->
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- CSS crítico inline para LCP más rápido -->
  <style>
    /* CSS crítico mínimo para evitar flash */
    body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, sans-serif; }
    .btn-primary { background: #e0b94d; color: #000; padding: 16px 32px; text-decoration: none; border-radius: 50px; }
    /* Evita saltos de menú en móvil antes de cargar CSS */
    #nav-links { display: flex; align-items: center; gap: 1.3rem; }
    @media (max-width: 780px) {
      #nav-links { display: none; }
    }
  </style>

  <!-- Preload recursos críticos -->
  <link rel="preload" href="../css/styles.css" as="style">
  <link rel="preload" href="../css/reset.css" as="style">
  <link rel="preload" href="../css/global-modern.css" as="style">
  <link rel="preload" href="../css/marketing-enhancements.css" as="style">
  <link rel="preload" href="../js/lazy-loading.js" as="script">
  <link rel="preload" href="../js/cart.js" as="script">
  <link rel="modulepreload" href="../js/nav.js">

  <!-- DNS prefetch para recursos externos -->
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Resource hints para performance -->
  <link rel="prefetch" href="./api/">
  <link rel="prefetch" href="./supplements.php">
  <link rel="prefetch" href="./packs.php">

  <!-- Fallback for browsers without JS -->
  <noscript>
    <link rel="stylesheet" href="../css/reset.css">
    <link rel="stylesheet" href="../css/styles.css">
  </noscript>

  <!-- Additional SEO Meta Tags -->
  <meta name="format-detection" content="telephone=no" />
  <meta name="theme-color" content="#e0b94d" />
  <meta name="msapplication-TileColor" content="#e0b94d" />
  <meta name="apple-mobile-web-app-status-bar-style" content="default" />

  <link rel="icon" type="image/svg+xml" href="../images/<?= htmlspecialchars($uniqueFavicon); ?>" />
  <link rel="apple-touch-icon" href="/images/<?= htmlspecialchars($uniqueFavicon); ?>" />

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($description) ?>" />
  <meta property="og:image" content="/images/<?= htmlspecialchars($uniqueFavicon) ?>" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:url" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?><?= $_SERVER['REQUEST_URI'] ?>" />
  <meta property="og:site_name" content="AlphaSupps" />
  <meta property="og:locale" content="es_ES" />

  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>" />
  <meta name="twitter:image" content="/images/<?= htmlspecialchars($uniqueFavicon) ?>" />
  <meta name="twitter:site" content="@alphasupps" />

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "AlphaSupps",
    "url": "https://alphasupps.com",
    "logo": "https://alphasupps.com/images/favicon_e0b94d.svg",
    "description": "Tienda de suplementos premium con calidad y ciencia para tu rendimiento deportivo.",
    "foundingDate": "2024",
    "contactPoint": {
      "@type": "ContactPoint",
      "contactType": "customer service",
      "availableLanguage": "Spanish"
    },
    "sameAs": [
      "https://www.instagram.com/alphasupps",
      "https://www.facebook.com/alphasupps"
    ],
    "hasOfferCatalog": {
      "@type": "OfferCatalog",
      "name": "Catálogo de Suplementos",
      "itemListElement": [
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Product",
            "name": "Proteínas",
            "description": "Proteínas de alta calidad para el desarrollo muscular"
          }
        },
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Product",
            "name": "Creatina",
            "description": "Suplementos de creatina para mejorar el rendimiento"
          }
        },
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Product",
            "name": "Aminoácidos",
            "description": "Aminoácidos esenciales para la recuperación muscular"
          }
        }
      ]
    }
  }
  </script>

  <?php
  global $pageCss;
  foreach ($pageCss as $file) {
      echo '<link rel="stylesheet" href="../css/' . $file . '.css" />' . PHP_EOL;
  }
  ?>

  <?php if (!empty($isAdminPanel)): ?>
    <link rel="stylesheet" href="../css/admin.css">
  <?php endif; ?>

  <meta name="stripe-link-label" content="off">
  <meta name="stripe-account" content="opt-out">

  <script>
    window.isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
    window.BASE_URL = '../';
  </script>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400&family=Poppins:wght@800&display=swap" rel="stylesheet">

  <!-- Performance scripts -->
  <script defer src="../js/lazy-loading.js"></script>
  <script defer src="../js/cart.js"></script>
  <script defer src="../js/cookies.js"></script>
  <script defer src="../js/filters.js"></script>
  <script defer src="../js/newsletter.js"></script>
  <script defer src="../js/nav.js"></script>
  <script defer src="../js/modal.js"></script>

  <!-- DIAGNÓSTICO: CSS para identificar elementos sin background definido -->
  <style>
  /* Diagnóstico temporal - resaltar elementos sin background */
  * {
    /* Quitar esta línea después de diagnosticar */
    /* background: rgba(255, 0, 0, 0.1) !important; */
  }

  /* Asegurar que el body siempre tenga background oscuro */
  body {
    background: #0E0E0E !important;
    color: #F5F5F5 !important;
  }

  /* Asegurar que los contenedores principales tengan background */
  main, section, div, article, header, footer {
    background: inherit;
  }

  /* Elementos que pueden aparecer en blanco */
  .card, .panel, .container, .wrapper, .content,
  .form-container, .modal-content, .popup, .overlay {
    background: var(--bg-panel, #1A1A1A) !important;
  }

  /* Elementos de carga y placeholders */
  .loading, .placeholder, .skeleton {
    background: var(--bg-light, #222222) !important;
  }

  /* Evitar zonas blancas en elementos vacíos */
  .empty, .no-content, .error-state {
    background: var(--bg-panel, #1A1A1A) !important;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  </style>

  <style>
:root {
  /* Variables principales - con fallbacks */
  --accent: <?= $accent ?? '#e0b94d' ?>;              /* Dorado fuerte */
  --accent-light: <?= $accent_light ?? '#f0c75e' ?>;  /* Dorado suave */
  --gold: <?= $gold ?? '#e0b94d' ?>;

  /* TIPOGRAFÍA */
  --font-body: "Inter", sans-serif;
  --font-title: "Poppins", sans-serif;
  --font-primary: "Inter", sans-serif;   /* Para reset.css */

  /* PALETA OSCURA PREMIUM */
  --bg-dark: #0E0E0E;          /* Fondo general */
  --dark-bg: #0B0B0B;          /* Zonas profundas */
  --dark-bg-alt: #141414;      /* Variación suave */
  --bg-primary: #0E0E0E;       /* Para reset.css */
  --bg-secondary: #141414;     /* Para reset.css */

  /* PANELES / TARJETAS */
  --bg-panel: #1A1A1A;         /* Tarjetas, bloques */
  --bg-light: #222222;         /* Inputs, hover */

  /* TEXTO */
  --text: #F5F5F5;
  --text-light: #D4D4D4;
  --text-muted: #AFAFAF;
  --text-primary: #F5F5F5;      /* Para reset.css */
  --text-base: 16px;            /* Para reset.css */

  /* BORDES PREMIUM */
  --color-border: rgba(255,255,255,0.06);
  --border: 1px solid rgba(255,255,255,0.06);
  --border-primary: rgba(255,255,255,0.06);  /* Para reset.css */
  --border-hover: rgba(255,255,255,0.12);    /* Para reset.css */

  /* SOMBRAS */
  --shadow: 0 8px 25px rgba(0,0,0,0.40);
  --shadow-gold: <?= $shadow_gold ?? '0 8px 25px rgba(224, 180, 77, 0.3)' ?>;
  --shadow-gold-hover: <?= $shadow_gold_hover ?? '0 12px 35px rgba(224, 180, 77, 0.4)' ?>;

  /* COLORES PRIMARIOS */
  --primary-gold: <?= $accent ?? '#e0b94d' ?>;           /* Para reset.css */
  --primary-gold-light: <?= $accent_light ?? '#f0c75e' ?>; /* Para reset.css */

  /* ESPACIADO */
  --space-4: 1rem;     /* 16px */
  --space-6: 1.5rem;   /* 24px */
  --space-8: 2rem;     /* 32px */

  /* TRANSICIONES */
  --transition-fast: 0.3s ease;
  --transition-normal: 0.3s ease;

  /* ESTADOS DE INTERACCIÓN */
  --state-hover-transform: translateY(-2px);
  --state-active-transform: translateY(0);
  --state-focus-shadow: 0 0 0 3px rgba(224, 180, 77, 0.3);

  /* COMPONENTES */
  --button-height: 44px;
  --card-border-radius: 12px;
  --border-width: 1px;
  --radius-sm: 6px;
  --radius-large: 16px;

  /* COLORES ADICIONALES */
  --color-primary: <?= $accent ?>;           /* Alias para accent */
  --color-primary-light: <?= $accent_light ?>; /* Alias para accent-light */
  --color-black: #000000;
  --color-surface-alt: #1A1A1A;
  --color-text: #F5F5F5;
  --color-text-secondary: #D4D4D4;

  /* FUENTES ADICIONALES */
  --font-display: "Poppins", sans-serif;

  /* TAMAÑOS DE TEXTO */
  --text-xs: 12px;
  --text-sm: 14px;
  --text-lg: 18px;
  --text-xl: 24px;

  /* ESPACIADO ADICIONAL */
  --space-xs: 0.25rem;   /* 4px */
  --space-sm: 0.5rem;    /* 8px */
  --space-md: 1rem;      /* 16px */
  --space-lg: 1.5rem;    /* 24px */
  --space-xl: 2rem;      /* 32px */

  /* ESPACIADO NUMÉRICO (para utilities) */
  --space-1: 0.25rem;    /* 4px */
  --space-2: 0.5rem;     /* 8px */
  --space-3: 0.75rem;    /* 12px */
  --space-5: 1.25rem;    /* 20px */
  --space-10: 2.5rem;    /* 40px */
  --space-12: 3rem;      /* 48px */
  --space-16: 4rem;      /* 64px */
  --space-20: 5rem;      /* 80px */
  --space-24: 6rem;      /* 96px */

  /* ESPACIADO NEGATIVO */
  --space-neg-1: -0.25rem;   /* -4px */
  --space-neg-2: -0.5rem;    /* -8px */
  --space-neg-3: -0.75rem;   /* -12px */

  /* SOMBRAS ADICIONALES */
  --shadow-primary: 0 8px 25px rgba(0,0,0,0.40);
  --shadow-gold: 0 8px 25px rgba(224, 180, 77, 0.3);

  /* GRADIENTES */
  --gradient-primary: linear-gradient(135deg, var(--primary-gold) 0%, var(--primary-gold-light) 100%);
  --gradient-secondary: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);

  /* PARA PRODUCT-LAYOUT.CSS */
  --bg: #0E0E0E;
  --card: #1A1A1A;
  --success: #10B981;

  /* PARA MODAL.CSS */
  --bg-card: #1A1A1A;
  --border-light: rgba(255,255,255,0.06);
  --text-dim: #AFAFAF;

  /* REDONDEO + TRANSICIÓN */
  --radius: 10px;
  --transition: 0.3s ease;
  --color-bg-main: #f9f9f9;
  --color-bg-panel: #ffffff;
  --color-text-main: #2a2a2a;
  --color-border: #dcdcdc;
  --color-accent-gold: #f0c75e;
  --color-accent-light: #ffe2a4;
}

</style>


  <link rel="manifest" href="/manifest.json">

  <meta name="application-name" content="AlphaSupps">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="AlphaSupps">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="msapplication-TileColor" content="<?= $accent ?>">
  <meta name="msapplication-config" content="/browserconfig.xml">

  <!-- Service Worker Registration -->
  <script>
    // Registrar Service Worker para PWA
    if ('serviceWorker' in navigator) {
      const swUrl = '/js/sw.js';

      function attachUpdateListener(registration) {
        registration.addEventListener('updatefound', function() {
          const newWorker = registration.installing;
          if (newWorker) {
            newWorker.addEventListener('statechange', function() {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // Nueva versión disponible
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
    }

    window.addEventListener('load', function() {
      if (window.matchMedia('(display-mode: standalone)').matches) {
        document.body.classList.add('pwa-mode');
      }
    });

    <?php require_once '../components/pwa-banner.php'; ?>
  </script>

  <!-- Bloqueo básico de clic derecho y selección de texto (bypassable en cliente) -->
  <script>
    (function() {
      document.addEventListener('contextmenu', function(e) { e.preventDefault(); }, { capture: true });
      document.addEventListener('selectstart', function(e) { e.preventDefault(); }, { capture: true });
      document.addEventListener('copy', function(e) { e.preventDefault(); }, { capture: true });
    })();
  </script>

</head>
