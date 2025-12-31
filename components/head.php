<?php require_once __DIR__ . '/head-context.php'; ?>

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

  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <link rel="alternate" hreflang="es" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <?php if (!empty($keywords)): ?>
    <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>" />
  <?php endif; ?>

  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <style>
    body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, sans-serif; }
    .btn-primary { background: #e0b94d; color: #000; padding: 16px 32px; text-decoration: none; border-radius: 50px; }
    #nav-links { display: flex; align-items: center; gap: 1.3rem; }
    @media (max-width: 780px) {
      #nav-links { display: none; }
    }
  </style>

  <link rel="preload" href="../css/styles.css" as="style">
  <link rel="preload" href="../css/reset.css" as="style">
  <link rel="preload" href="../css/global-modern.css" as="style">
  <link rel="preload" href="../css/marketing-enhancements.css" as="style">
  <link rel="preload" href="../js/lazy-loading.js" as="script">
  <link rel="preload" href="../js/cart.js" as="script">
  <link rel="modulepreload" href="../js/nav.js">

  <noscript>
    <link rel="stylesheet" href="../css/reset.css">
    <link rel="stylesheet" href="../css/styles.css">
  </noscript>

  <meta name="format-detection" content="telephone=no" />
  <meta name="msapplication-TileColor" content="<?= $accent ?>" />
  <meta name="apple-mobile-web-app-status-bar-style" content="default" />

  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl . '/images/' . $uniqueFavicon); ?>" />
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseUrl . '/images/' . $uniqueFavicon); ?>" />

  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($description) ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>" />
  <meta property="og:site_name" content="AlphaSupps" />
  <meta property="og:locale" content="es_ES" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>" />
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>" />
  <meta name="twitter:site" content="@alphasupps" />

  <?php include __DIR__ . '/schema-org.php'; ?>

  <?php
  global $pageCss;
  $pageCss = $pageCss ?? [];
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

  <script defer src="../js/lazy-loading.js"></script>
  <script defer src="../js/cart.js"></script>
  <script defer src="../js/cookies.js"></script>
  <script defer src="../js/filters.js"></script>
  <script defer src="../js/newsletter.js"></script>
  <script defer src="../js/nav.js"></script>
  <script defer src="../js/modal.js"></script>
  <script defer src="../js/sw-register.js"></script>

  <style>
:root {
  --accent: <?= $accent ?? '#e0b94d' ?>;
  --accent-light: <?= $accent_light ?? '#f0c75e' ?>;
  --gold: <?= $gold ?? '#e0b94d' ?>;

  --font-body: "Inter", sans-serif;
  --font-title: "Poppins", sans-serif;
  --font-primary: "Inter", sans-serif;

  --bg-dark: #0E0E0E;
  --dark-bg: #0B0B0B;
  --dark-bg-alt: #141414;
  --bg-primary: #0E0E0E;
  --bg-secondary: #141414;

  --bg-panel: #1A1A1A;
  --bg-light: #222222;

  --text: #F5F5F5;
  --text-light: #D4D4D4;
  --text-muted: #AFAFAF;
  --text-primary: #F5F5F5;
  --text-base: 16px;

  --color-border: rgba(255,255,255,0.06);
  --border: 1px solid rgba(255,255,255,0.06);
  --border-primary: rgba(255,255,255,0.06);
  --border-hover: rgba(255,255,255,0.12);

  --shadow: 0 8px 25px rgba(0,0,0,0.40);
  --shadow-gold: <?= $shadow_gold ?? '0 8px 25px rgba(224, 180, 77, 0.3)' ?>;
  --shadow-gold-hover: <?= $shadow_gold_hover ?? '0 12px 35px rgba(224, 180, 77, 0.4)' ?>;

  --primary-gold: <?= $accent ?? '#e0b94d' ?>;
  --primary-gold-light: <?= $accent_light ?? '#f0c75e' ?>;

  --space-4: 1rem;
  --space-6: 1.5rem;
  --space-8: 2rem;

  --transition-fast: 0.3s ease;
  --transition-normal: 0.3s ease;

  --state-hover-transform: translateY(-2px);
  --state-active-transform: translateY(0);
  --state-focus-shadow: 0 0 0 3px rgba(224, 180, 77, 0.3);

  --button-height: 44px;
  --card-border-radius: 12px;
  --border-width: 1px;
  --radius-sm: 6px;
  --radius-large: 16px;

  --color-primary: <?= $accent ?>;
  --color-primary-light: <?= $accent_light ?>;
  --color-black: #000000;
  --color-surface-alt: #1A1A1A;
  --color-text: #F5F5F5;
  --color-text-secondary: #D4D4D4;

  --font-display: "Poppins", sans-serif;

  --text-xs: 12px;
  --text-sm: 14px;
  --text-lg: 18px;
  --text-xl: 24px;

  --space-xs: 0.25rem;
  --space-sm: 0.5rem;
  --space-md: 1rem;
  --space-lg: 1.5rem;
  --space-xl: 2rem;

  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-5: 1.25rem;
  --space-10: 2.5rem;
  --space-12: 3rem;
  --space-16: 4rem;
  --space-20: 5rem;
  --space-24: 6rem;

  --space-neg-1: -0.25rem;
  --space-neg-2: -0.5rem;
  --space-neg-3: -0.75rem;

  --shadow-primary: 0 8px 25px rgba(0,0,0,0.40);

  --gradient-primary: linear-gradient(135deg, var(--primary-gold) 0%, var(--primary-gold-light) 100%);
  --gradient-secondary: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);

  --bg: #0E0E0E;
  --card: #1A1A1A;
  --success: #10B981;

  --bg-card: #1A1A1A;
  --border-light: rgba(255,255,255,0.06);
  --text-dim: #AFAFAF;

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

  <?php require_once __DIR__ . '/pwa-banner.php'; ?>

  <script>
    (function() {
      document.addEventListener('contextmenu', function(e) { e.preventDefault(); }, { capture: true });
      document.addEventListener('selectstart', function(e) { e.preventDefault(); }, { capture: true });
      document.addEventListener('copy', function(e) { e.preventDefault(); }, { capture: true });
    })();
  </script>

</head>
