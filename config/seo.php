<?php

/* ============================================================
 * 1. DETECCIÓN DE PÁGINA ACTUAL
 * ============================================================ */

$path = $_SERVER['SCRIPT_FILENAME'];
$pathParts = explode('/', $path);
$directory = $pathParts[count($pathParts) - 2] ?? '';
$filename = basename($path, '.php');

$currentPage = $filename;

// mapas para admin
if ($directory === 'admin') {
    $adminPageMap = [
        'supplements' => 'supplements_admin',
        'user'        => 'user',
        'order'       => 'order'
    ];

    $currentPage = $adminPageMap[$filename] ?? $filename;
}


/* ============================================================
 * 2. ESTRUCTURA DE PÁGINAS AGRUPADAS
 * ============================================================ */

$pages = [

    /* ============================================================
     * VIEWS — PÁGINAS PRINCIPALES
     * ============================================================ */
    "views" => [

        "index" => [
            "name"        => "Página de Inicio",
            "title"       => "Inicio | AlphaSupps",
            "description" => "Bienvenido a AlphaSupps.",
            "keywords"    => "alphasupps, suplementos deportivos",
            "canonical"   => "/views/index/",
            "css"         => ["cards", "landing"]
        ],

        "supplements" => [
            "name"        => "Catálogo de Suplementos",
            "title"       => "Suplementos | AlphaSupps",
            "description" => "Descubre nuestro catálogo.",
            "keywords"    => "suplementos, proteínas, creatina",
            "canonical"   => "/views/supplements/",
            "css"         => ["supplements", "cards"]
        ],

        "supplement" => [
            "name"        => "Producto Individual",
            "title"       => "Suplemento | AlphaSupps",
            "description" => "Información del suplemento.",
            "keywords"    => "suplemento, ficha producto",
            "canonical"   => "/views/supplement/",
            "css"         => ["product-layout", "subscription"]
        ],

        "packs" => [
            "name"        => "Packs",
            "title"       => "Packs | AlphaSupps",
            "description" => "Packs según tu objetivo.",
            "keywords"    => "packs suplementos",
            "canonical"   => "/views/packs/",
            "css"         => ["packs", "cards", "hero"]
        ],

        "pack" => [
            "name"        => "Pack Individual",
            "title"       => "Pack | AlphaSupps",
            "description" => "{{pack_description}}",
            "keywords"    => "pack, {{pack_name}}",
            "canonical"   => "/views/pack/?id={{id}}",
           "css"         => ["product-layout", "subscription"]
        ],
        "subscriptions" => [
            "name"        => "Mis suscripciones",
            "title"       => "Mis suscripciones | AlphaSupps",
            "description" => "Gestiona tus suscripciones y controla tu frecuencia de envio.",
            "keywords"    => "suscripciones, gestion, alpha supps",
            "canonical"   => "/views/subscriptions/",
            "css"         => ["subscriptions"]
        ],
        "custom-pack" => [
            "name"        => "Pack Individual",
            "title"       => "Pack | AlphaSupps",
            "description" => "{{pack_description}}",
            "keywords"    => "pack, {{pack_name}}",
            "canonical"   => "/views/pack/?id={{id}}",
           "css"         => ["custom-pack"]
        ],

        "alphabox" => [
            "name"        => "AlphaBox",
            "title"       => "AlphaBox | AlphaSupps",
            "description" => "Suscripción mensual.",
            "keywords"    => "suscripción suplementos",
            "canonical"   => "/views/alphabox/",
            "css"         => ["alphaBox"]
        ],

        "blog" => [
            "name"        => "Blog",
            "title"       => "Blog | AlphaSupps",
            "description" => "Artículos y guías.",
            "keywords"    => "blog suplementos",
            "canonical"   => "/views/blog/",
            "css"         => ["cards", "blog"]
        ],

        "post" => [
            "name"        => "Artículo",
            "title"       => "Artículo | AlphaSupps",
            "description" => "Contenido informativo.",
            "keywords"    => "artículo blog",
            "canonical"   => "/views/post/",
            "css"         => ["post", "blog"]
        ],

        "cart" => [
            "name"        => "Carrito",
            "title"       => "Carrito | AlphaSupps",
            "description" => "Revisa tus productos.",
            "keywords"    => "carrito compra",
            "canonical"   => "/views/cart/",
            "css"         => ["cart", "cards"]
        ],

        "login" => [
            "name"        => "Login",
            "title"       => "Iniciar Sesión | AlphaSupps",
            "description" => "Accede a tu cuenta.",
            "keywords"    => "login usuario",
            "canonical"   => "/views/login/",
            "css"         => ["login"]
        ],

        "register" => [
            "name"        => "Registro",
            "title"       => "Registro | AlphaSupps",
            "description" => "Crea tu cuenta.",
            "keywords"    => "registro usuario",
            "canonical"   => "/views/register/",
            "css"         => ["login"]
        ],

        "collection" => [
            "name"        => "Colecciones",
            "title"       => "Colecciones | AlphaSupps",
            "description" => "Explora colecciones.",
            "keywords"    => "colecciones suplementos",
            "canonical"   => "/views/collections/",
            "css"         => ["collection", "cards"]
        ],

        "story" => [
            "name"        => "Historia",
            "title"       => "Historia | AlphaSupps",
            "description" => "Conoce nuestros valores.",
            "keywords"    => "historia marca",
            "canonical"   => "/views/story/",
            "css"         => ["story"]
        ],

        "track" => [
            "name"        => "Seguimiento de pedido",
            "title"       => "Seguimiento | AlphaSupps",
            "description" => "Consulta el estado y tracking de tu pedido.",
            "keywords"    => "seguimiento, tracking pedido",
            "canonical"   => "/views/track/",
            "css"         => []
        ],
    ],

    /* ============================================================
     * POLICIES — PÁGINAS LEGALES
     * ============================================================ */
    "policies" => [

        "faq" => [
            "name"        => "Preguntas Frecuentes",
            "title"       => "FAQ | AlphaSupps",
            "description" => "Respuestas a dudas comunes.",
            "keywords"    => "preguntas frecuentes",
            "canonical"   => "/views/faq/",
            "css"         => ["policies"]
        ],

        "privacy-policy" => [
            "name"        => "Política de Privacidad",
            "title"       => "Privacidad | AlphaSupps",
            "description" => "Protección de datos personales.",
            "keywords"    => "política privacidad",
            "canonical"   => "/views/privacy-policy/",
            "css"         => ["policies"]
        ],

        "cookies" => [
            "name"        => "Política de Cookies",
            "title"       => "Cookies | AlphaSupps",
            "description" => "Información sobre cookies.",
            "keywords"    => "cookies",
            "canonical"   => "/views/cookies/",
            "css"         => ["cookies", "policies"]
        ],

        "legal-advise" => [
            "name"        => "Aviso Legal",
            "title"       => "Aviso Legal | AlphaSupps",
            "description" => "Información legal del sitio.",
            "keywords"    => "aviso legal",
            "canonical"   => "/views/legal-advise/",
            "css"         => ["policies"]
        ],

        "terms" => [
            "name"        => "Términos y Condiciones",
            "title"       => "Términos | AlphaSupps",
            "description" => "Condiciones de uso.",
            "keywords"    => "términos y condiciones",
            "canonical"   => "/views/terms/",
            "css"         => ["policies"]
        ],

        "desistimiento" => [
            "name"        => "Derecho de Desistimiento",
            "title"       => "Desistimiento | AlphaSupps",
            "description" => "Cómo devolver productos.",
            "keywords"    => "desistimiento",
            "canonical"   => "/views/desistimiento/",
            "css"         => ["policies"]
        ],

        "unsuscribe" => [
            "name"        => "Darse de Baja",
            "title"       => "Baja | AlphaSupps",
            "description" => "Cancelar notificaciones.",
            "keywords"    => "baja suscripción",
            "canonical"   => "/views/unsuscribe/",
            "css"         => ["policies"]
        ],

        "envios" => [
            "name"        => "Envíos",
            "title"       => "Envíos | AlphaSupps",
            "description" => "Información sobre envíos.",
            "keywords"    => "envíos",
            "canonical"   => "/views/envios/",
            "css"         => ["policies"]
        ],
        "contact" => [
            "name"        => "Envíos",
            "title"       => "Envíos | AlphaSupps",
            "description" => "Información sobre envíos.",
            "keywords"    => "envíos",
            "canonical"   => "/views/envios/",
            "css"         => ["contact"]
        ],
    ],

    /* ============================================================
     * ADMIN — PANEL ADMINISTRATIVO
     * ============================================================ */
    "admin" => [

        "dashboard" => [
            "name"      => "Dashboard",
            "title"     => "Admin - Dashboard",
            "description" => "Panel administrativo.",
            "keywords"  => "admin dashboard",
            "canonical" => "/admin/dashboard/",
            "css"       => ["admin"]
        ],

        "brands" => [
            "name"      => "Marcas",
            "title"     => "Admin - Marcas",
            "description" => "Gestión de marcas.",
            "keywords"  => "admin marcas",
            "canonical" => "/admin/brands/",
            "css"       => ["admin"]
        ],

        "categories" => [
            "name"      => "Categorías",
            "title"     => "Admin - Categorías",
            "description" => "Gestión de categorías.",
            "keywords"  => "admin categorías",
            "canonical" => "/admin/categories/",
            "css"       => ["admin"]
        ],

        "posts" => [
            "name"      => "Posts",
            "title"     => "Admin - Posts",
            "description" => "Gestión del blog.",
            "keywords"  => "admin blog",
            "canonical" => "/admin/posts/",
            "css"       => ["admin"]
        ],

        "messages" => [
            "name"      => "Mensajes",
            "title"     => "Admin - Mensajes",
            "description" => "Gestión de consultas.",
            "keywords"  => "admin mensajes",
            "canonical" => "/admin/messages/",
            "css"       => ["admin"]
        ],

        "orders" => [
            "name"      => "Pedidos",
            "title"     => "Admin - Pedidos",
            "description" => "Gestión de pedidos.",
            "keywords"  => "admin pedidos",
            "canonical" => "/admin/orders/",
            "css"       => ["admin"]
        ],

        "supplements_admin" => [
            "name"      => "Suplementos",
            "title"     => "Admin - Suplementos",
            "description" => "Gestión del catálogo.",
            "keywords"  => "admin suplementos",
            "canonical" => "/admin/supplements/",
            "css"       => ["admin"]
        ],

        "profile" => [
            "name"      => "Perfil",
            "title"     => "Admin - Perfil",
            "description" => "Gestión del perfil.",
            "keywords"  => "admin perfil",
            "canonical" => "/admin/profile/",
            "css"       => ["admin"]
        ],

        "settings" => [
            "name"      => "Settings",
            "title"     => "Admin - Configuración",
            "description" => "Ajustes del sitio.",
            "keywords"  => "admin configuración",
            "canonical" => "/admin/settings/",
            "css"       => ["admin"]
        ],

        "sales" => [
            "name"      => "Ventas",
            "title"     => "Admin - Ventas",
            "description" => "Estadísticas de ventas.",
            "keywords"  => "admin ventas",
            "canonical" => "/admin/sales/",
            "css"       => ["admin"]
        ],

        "user" => [
            "name"      => "Usuario",
            "title"     => "Admin - Usuario",
            "description" => "Ficha de usuario.",
            "keywords"  => "admin usuario",
            "canonical" => "/admin/user/",
            "css"       => ["admin"]
        ],

        "order" => [
            "name"      => "Pedido",
            "title"     => "Admin - Pedido",
            "description" => "Detalle de pedido.",
            "keywords"  => "admin pedido",
            "canonical" => "/admin/order/",
            "css"       => ["admin"]
        ],
    ],

    /* ============================================================
     * REST — PÁGINAS SECUNDARIAS
     * ============================================================ */
    "rest" => [

        "forgot-password" => [
            "name"        => "Recuperar Contraseña",
            "title"       => "Recuperar Contraseña | AlphaSupps",
            "description" => "Recupera el acceso.",
            "keywords"    => "recuperar contraseña",
            "canonical"   => "/views/forgot-password/",
            "css"         => ["forgot", "login"]
        ],

        "reset_password" => [
            "name"        => "Nueva Contraseña",
            "title"       => "Nueva Contraseña | AlphaSupps",
            "description" => "Restablece tu clave.",
            "keywords"    => "reset password",
            "canonical"   => "/views/reset_password/",
            "css"         => ["forgot", "login"]
        ],

        "contact" => [
            "name"        => "Contacto",
            "title"       => "Contacto | AlphaSupps",
            "description" => "Escríbenos tus dudas.",
            "keywords"    => "contacto soporte",
            "canonical"   => "/views/contact/",
            "css"         => ["contact"]
        ],

        "invoice" => [
            "name"        => "Factura",
            "title"       => "Factura | AlphaSupps",
            "description" => "Consulta tu factura.",
            "keywords"    => "factura",
            "canonical"   => "/views/invoice/",
            "css"         => ["factura"]
        ],
    ]
];


/* ============================================================
 * 3. FUNCIÓN PARA BUSCAR EN SUBCLAVES
 * ============================================================ */

function findPageData($pages, $currentPage) {
    foreach ($pages as $group) {
        if (isset($group[$currentPage])) {
            return $group[$currentPage];
        }
    }
    return null;
}


/* ============================================================
 * 4. OBTENER LA PÁGINA ACTUAL
 * ============================================================ */

$pageData = findPageData($pages, $currentPage);


/* ============================================================
 * 5. CARGA DE CSS
 * ============================================================ */

$defaultCss = [
    'reset',
    'styles',
    'header',
    'footer',
    'buttons',
    'media',
    'modal',
    'toast'
];

$pageCss = $defaultCss;

if ($pageData && !empty($pageData['css'])) {
    $pageCss = array_merge($pageCss, $pageData['css']);
}

$pageCss = array_unique($pageCss);

?>
