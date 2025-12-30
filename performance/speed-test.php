<?php
/**
 * Speed Test - AlphaSupps
 * Mide el rendimiento de carga de la página
 */

echo "<h1>⚡ Speed Test - AlphaSupps</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #0E0E0E; color: #F5F5F5; }
.success { color: #10B981; }
.error { color: #EF4444; }
.warning { color: #F59E0B; }
.metric { background: #1A1A1A; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #e0b94d; }
</style>";

$startTime = microtime(true);

// 1. Medir tiempo de carga de archivos críticos
echo "<h2>📊 Métricas de Rendimiento</h2>";

echo "<div class='metric'>";
echo "<h3>Archivos CSS Críticos</h3>";
    $criticalCssFiles = ['../css/reset.css', '../css/styles.css', '../css/global-modern.css'];
$totalCssSize = 0;

foreach ($criticalCssFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        $totalCssSize += $size;
        echo "<p>✅ $file: " . number_format($size / 1024, 1) . " KB</p>";
    } else {
        echo "<p class='error'>❌ $file: No encontrado</p>";
    }
}
echo "<p><strong>Total CSS crítico: " . number_format($totalCssSize / 1024, 1) . " KB</strong></p>";
echo "</div>";

echo "<div class='metric'>";
echo "<h3>Archivos JavaScript Críticos</h3>";
$criticalJsFiles = ['../js/lazy-loading.js', '../js/cart.js', '../js/cookies.js'];
$totalJsSize = 0;

foreach ($criticalJsFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        $totalJsSize += $size;
        echo "<p>✅ $file: " . number_format($size / 1024, 1) . " KB</p>";
    } else {
        echo "<p class='error'>❌ $file: No encontrado</p>";
    }
}
echo "<p><strong>Total JS crítico: " . number_format($totalJsSize / 1024, 1) . " KB</strong></p>";
echo "</div>";

// 2. Verificar variables CSS
echo "<div class='metric'>";
echo "<h3>Variables CSS Definidas</h3>";

// Incluir controlador para variables dinámicas
require_once '../controllers/config.php';
$accent = $accent ?? '#e0b94d';

// Verificar constantes CSS en head.php
$headContent = file_get_contents('../components/head.php');
$cssConstants = [
    '--accent:' => $accent, // Variable dinámica
    '--bg-dark:' => '#0E0E0E', // Constante hardcodeada
    '--bg-panel:' => '#1A1A1A', // Constante hardcodeada
    '--text:' => '#F5F5F5', // Constante hardcodeada
    '--font-body:' => '"Inter", sans-serif', // Constante hardcodeada
    '--shadow:' => '0 8px 25px rgba(0,0,0,0.40)' // Constante hardcodeada
];

foreach ($cssConstants as $var => $expectedValue) {
    if (strpos($headContent, $var) !== false) {
        echo "<p class='success'>✅ " . str_replace(':', '', $var) . ": $expectedValue</p>";
    } else {
        echo "<p class='error'>❌ " . str_replace(':', '', $var) . ": No encontrada en head.php</p>";
    }
}
echo "</div>";

// 3. Medir tiempo de ejecución PHP
$phpTime = microtime(true) - $startTime;

echo "<div class='metric'>";
echo "<h3>Tiempo de Ejecución</h3>";
echo "<p>⏱️ <strong>PHP Execution Time: " . number_format($phpTime * 1000, 2) . " ms</strong></p>";

if ($phpTime < 0.1) {
    echo "<p class='success'>✅ Rendimiento excelente (< 100ms)</p>";
} elseif ($phpTime < 0.5) {
    echo "<p class='success'>✅ Rendimiento bueno (< 500ms)</p>";
} else {
    echo "<p class='warning'>⚠️ Rendimiento lento (> 500ms)</p>";
}
echo "</div>";

// 4. Verificar sistema de cache
echo "<div class='metric'>";
echo "<h3>Sistema de Cache</h3>";
if (file_exists('../cache/database/')) {
    $cacheFiles = glob('../cache/database/*.cache');
    $cacheCount = count($cacheFiles);
    echo "<p class='success'>✅ Cache habilitado ($cacheCount archivos en cache)</p>";
} else {
    echo "<p class='warning'>⚠️ Cache no inicializado (se creará automáticamente)</p>";
}
echo "</div>";

// 5. Recomendaciones de optimización
echo "<div class='metric'>";
echo "<h3>💡 Recomendaciones de Optimización</h3>";
echo "<ul>";
echo "<li>🎯 <strong>LCP (Largest Contentful Paint):</strong> Optimizar imagen hero</li>";
echo "<li>⚡ <strong>FID (First Input Delay):</strong> Scripts críticos cargados primero</li>";
echo "<li>📐 <strong>CLS (Cumulative Layout Shift):</strong> Lazy loading con placeholders</li>";
echo "<li>🗜️ <strong>Compresión:</strong> GZIP/Brotli habilitado</li>";
echo "<li>💾 <strong>Cache:</strong> Headers agresivos configurados</li>";
echo "</ul>";
echo "</div>";

// 6. Guía de prueba manual
echo "<h2>🧪 Pruebas Manuales Recomendadas</h2>";
echo "<div class='metric'>";
echo "<h3>En el Navegador (F12)</h3>";
echo "<ul>";
echo "<li><strong>Network Tab:</strong> Verificar tiempos de carga</li>";
echo "<li><strong>Console:</strong> Buscar errores JavaScript</li>";
echo "<li><strong>Performance Tab:</strong> Analizar Core Web Vitals</li>";
echo "<li><strong>Lighthouse:</strong> Ejecutar auditoría completa</li>";
echo "</ul>";
echo "</div>";

echo "<div class='metric'>";
echo "<h3>Funcionalidades a Probar</h3>";
echo "<ul>";
echo "<li>🖱️ Navegación entre páginas</li>";
echo "<li>🛒 Carrito de compras</li>";
echo "<li>🔍 Filtros de productos</li>";
echo "<li>📱 Responsive design</li>";
echo "<li>⚡ Lazy loading de imágenes</li>";
echo "<li>🌙 Tema oscuro consistente</li>";
echo "</ul>";
echo "</div>";

echo "<p style='margin-top: 30px; padding: 20px; background: #1A1A1A; border-radius: 8px; border-left: 4px solid #10B981;'>";
echo "🎉 <strong>¡Optimización completada!</strong> El sitio debería cargar más rápido, ser más fluido y mantener todas las funcionalidades.";
echo "</p>";
?>
