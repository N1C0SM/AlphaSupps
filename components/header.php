<?php
$isLoggedNav = isset($user) && $user;
$userHasSubscriptionsNav = $hasSubscriptions ?? false;
$isAdminUserNav = isset($admin) && $admin;
$inAdminPanelNav = isset($isAdminPanel) && $isAdminPanel && $isAdminUserNav;
$userNameNav = $isLoggedNav ? htmlspecialchars($user['name'] ?? 'Usuario') : '';
?>

<header class="main-header">
  <div class="nav-container">
    <a href="/" class="logo">
      <svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 100 100">
        <g fill="none" stroke="<?= $accent ?>" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="50" cy="50" r="46" />
          <path d="M50 18 L72 75 L28 75 Z" />
          <path d="M47 32 C49 38, 53 42, 51 47 C49 52, 43 56, 46 61 L56 72" />
        </g>
      </svg>
      <span class="brand-text">Alpha<span class="accent">Supps</span></span>
    </a>

    <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú de navegación" aria-expanded="false">
      <svg class="menu-icon" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <nav id="nav-links">
      <a href="/" class="nav-link">Inicio</a>
      <a href="../views/packs.php" class="nav-link">Packs</a>
      <a href="../views/supplements.php" class="nav-link">Suplementos</a>
      <a href="../views/alphabox.php" class="nav-link">AlphaBox</a>
      <?php if ($isLoggedNav && $userHasSubscriptionsNav): ?>
        <a href="../views/subscriptions.php" class="nav-link">Mis suscripciones</a>
      <?php endif; ?>
      <a href="../views/story.php" class="nav-link">Mi historia</a>
      <a href="../views/blog.php" class="nav-link">Blog</a>

      <a href="../views/cart.php" class="nav-link cart-btn" aria-label="Carrito">
        <svg class="cart-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 3h1.386l1.087.835.383 1.437M7.5 14.25h11.218c1.121-2.3
                2.1-4.684 2.924-7.138-5.5-.8-11-.8-16.536-1.84L7.5 14.25Z"></path>
          <circle cx="6" cy="20.25" r="0.75"></circle>
          <circle cx="18.75" cy="20.25" r="0.75"></circle>
        </svg>
        <span id="cart-count" class="cart-count">0</span>
      </a>

      <?php if ($isLoggedNav): ?>
        <div class="user-info">
          <span class="user-name">👋 <?= $userNameNav ?></span>
          <?php if ($isAdminUserNav && !$inAdminPanelNav): ?>
            <a href="../admin/dashboard.php" class="admin-link">Panel</a>
          <?php endif; ?>
          <a href="../controllers/user.php?action=logout" class="logout-btn">Salir</a>
        </div>
      <?php else: ?>
        <a href="../views/login.php" class="nav-link login-btn" aria-label="Iniciar sesión">
          <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26"
               class="user-icon" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round"
               stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </a>
      <?php endif; ?>
    </nav>

  </div>
</header>

<?php if (isset($admin) && $admin && isset($isAdminPanel) && $isAdminPanel): ?>
  <div id="adminSidebar" class="admin-sidebar">
    <div class="admin-nav">
      <a href="../admin/dashboard.php" class="nav-link">📊 Panel General</a>
      <a href="../admin/users.php" class="nav-link">👥 Usuarios</a>
      <a href="../admin/orders.php" class="nav-link">📦 Pedidos</a>
      <a href="../admin/sales.php" class="nav-link">💰 Ventas</a>
      <a href="../admin/categories.php" class="nav-link">🏷 Categorías</a>
      <a href="../admin/supplements.php" class="nav-link">💊 Suplementos</a>
      <a href="../admin/brands.php" class="nav-link">🏷 Marcas</a>
      <a href="../admin/posts.php" class="nav-link">📝 Posts</a>
      <a href="../admin/settings.php" class="nav-link">⚙️ Configuración</a>
      <a href="../admin/messages.php" class="nav-link">📬 Mensajes</a>
      <hr>
      <a href="../admin/profile.php" class="nav-link">👤 Perfil</a>
      <a href="/" class="nav-link">🏠 Ir al sitio</a>
      <a href="../controllers/user.php?action=logout" class="nav-link">🚪 Cerrar sesión</a>
    </div>
  </div>
<?php endif; ?>


<!-- ========== NAVIGATION SCRIPT ========== -->
<script>
  const isAdmin = <?= $isAdminUserNav ? 'true' : 'false' ?>;
  const inPanel = <?= $inAdminPanelNav ? 'true' : 'false' ?>;

  const nav = document.getElementById("nav-links");
  const sidebar = document.getElementById("adminSidebar");
  const toggle = document.getElementById("menu-toggle");

  function menuAdminMobile() {
    return `
    <a href="/" class="nav-link">🏠 Inicio</a>
    <a href="../views/packs.php" class="nav-link">📦 Packs</a>
    <a href="../views/supplements.php" class="nav-link">💊 Suplementos</a>
    <a href="../views/alphabox.php" class="nav-link">⚡ AlphaBox</a>
    <a href="../views/story.php" class="nav-link">📘 Mi historia</a>
    <a href="../views/blog.php" class="nav-link">📝 Blog</a>

    <hr>

    <a href="../admin/dashboard.php" class="nav-link">📊 Panel Admin</a>
    <a href="../admin/users.php" class="nav-link">👥 Usuarios</a>
    <a href="../admin/orders.php" class="nav-link">📦 Pedidos</a>
    <a href="../admin/sales.php" class="nav-link">💰 Ventas</a>
    <a href="../admin/categories.php" class="nav-link">🏷 Categorías</a>
    <a href="../admin/supplements.php" class="nav-link">💊 Suplementos</a>
    <a href="../admin/brands.php" class="nav-link">🏷 Marcas</a>
    <a href="../admin/posts.php" class="nav-link">📝 Posts</a>
    <a href="../admin/settings.php" class="nav-link">⚙ Configuración</a>
    <a href="../admin/messages.php" class="nav-link">📬 Mensajes</a>
    <hr>

    <a href="../controllers/user.php?action=logout" class="nav-link">🚪 Cerrar sesión</a>
  `;
  }

  function renderMenu() {
    if (isAdmin && inPanel) {
      if (window.innerWidth > 780) {
        sidebar?.classList.add("active");
        return;
      }
      sidebar?.classList.remove("active");
      nav.innerHTML = menuAdminMobile();
    }
  }

  renderMenu();
  window.addEventListener("resize", renderMenu);

  toggle?.addEventListener("click", () => {
    const isActive = nav.classList.toggle("active");
    toggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
  });
</script>
