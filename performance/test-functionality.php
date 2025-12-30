<?php
/**
 * Script de Prueba de Funcionalidades - AlphaSupps
 * Verifica que todo funcione correctamente después de las optimizaciones
 */

echo "<h1>🧪 Pruebas de Funcionalidad - AlphaSupps</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #0E0E0E; color: #F5F5F5; } .success { color: #10B981; } .error { color: #EF4444; } .warning { color: #F59E0B; }</style>";

echo "<h2>1. Conexión a Base de Datos</h2>";
try {
    require_once '../models/db.php';
    $conn = connectToDatabase();
    if ($conn) {
        echo "<p class='success'>✅ Conexión exitosa a MySQL</p>";
        $conn->close();
    } else {
        echo "<p class='error'>❌ Error de conexión a MySQL</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Archivos CSS</h2>";
$cssFiles = [
    '../css/reset.css',
    '../css/styles.css',
    '../css/global-modern.css',
    '../css/header.css',
    '../css/footer.css'
];

foreach ($cssFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file no encontrado</p>";
    }
}

// 3. Verificar archivos JS críticos
echo "<h2>3. Archivos JavaScript</h2>";
$jsFiles = [
    '../js/lazy-loading.js',
    '../js/cart.js',
    '../js/nav.js'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file no encontrado</p>";
    }
}

// 4. Verificar configuración SEO
echo "<h2>4. Configuración SEO</h2>";
require_once '../config/seo.php';
if (isset($pages) && is_array($pages)) {
    echo "<p class='success'>✅ Configuración SEO cargada correctamente</p>";
    echo "<p>📊 Páginas configuradas: " . count($pages['views']) . " vistas, " . count($pages['policies']) . " políticas</p>";
} else {
    echo "<p class='error'>❌ Error en configuración SEO</p>";
}

// 5. Verificar modelos principales
echo "<h2>5. Modelos de Datos</h2>";
$modelFiles = [
    '../models/pack.php',
    '../models/user.php',
    '../models/settings.php'
];

foreach ($modelFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file no encontrado</p>";
    }
}

// 6. Verificar componentes principales
echo "<h2>6. Componentes</h2>";
$componentFiles = [
    '../components/head.php',
    '../components/header.php',
    '../components/footer.php'
];

foreach ($componentFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file no encontrado</p>";
    }
}

// 7. Verificar páginas principales
echo "<h2>7. Páginas Principales</h2>";
$pageFiles = [
    '../views/index.php',
    '../views/supplements.php',
    '../views/packs.php'
];

foreach ($pageFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file no encontrado</p>";
    }
}

// 8. Verificar sistema de cache
echo "<h2>8. Sistema de Cache</h2>";
if (file_exists('cache/database/')) {
    echo "<p class='success'>✅ Directorio de cache existe</p>";
} else {
    echo "<p class='warning'>⚠️ Directorio de cache no existe (se creará automáticamente)</p>";
}

// 9. Verificar optimización de imágenes
echo "<h2>9. Optimización de Imágenes</h2>";
if (file_exists('../includes/image-optimizer.php')) {
    echo "<p class='success'>✅ Sistema de optimización de imágenes disponible</p>";
} else {
    echo "<p class='error'>❌ Sistema de optimización de imágenes no encontrado</p>";
}

// 10. Verificar PWA
echo "<h2>10. Progressive Web App</h2>";
$pwaFiles = [
    '../manifest.json',
    '../js/sw.js',
    '../images/icon-192x192.png',
    '../images/icon-512x512.png'
];

foreach ($pwaFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file no encontrado</p>";
    }
}

echo "<h2>🎯 Resumen de Pruebas</h2>";
echo "<p>Si todas las pruebas anteriores muestran ✅, el sitio debería funcionar correctamente.</p>";
echo "<p>Para probar la velocidad, accede a <a href='http://localhost:8888/AlphaSupps/views/index.php' style='color: #e0b94d;'>http://localhost:8888/AlphaSupps/views/index.php</a></p>";
echo "<p>Usa las herramientas de desarrollo del navegador (F12) para verificar:</p>";
echo "<ul>";
echo "<li>✅ Tiempo de carga de página</li>";
echo "<li>✅ Errores de JavaScript en consola</li>";
echo "<li>✅ Errores de red (404, etc.)</li>";
echo "<li>✅ Core Web Vitals (LCP, FID, CLS)</li>";
echo "</ul>";
?>
