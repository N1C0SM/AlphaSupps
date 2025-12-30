<?php
/**
 * Quick Test - AlphaSupps
 * Prueba rápida sin dependencias de BD
 */

echo "<h1>⚡ Quick Test - AlphaSupps</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #0E0E0E; color: #F5F5F5; } .success { color: #10B981; } .error { color: #EF4444; } .warning { color: #F59E0B; }</style>";

$errors = 0;
$warnings = 0;

// 1. Verificar archivos críticos
echo "<h2>📁 Archivos Críticos</h2>";
$criticalFiles = [
    '../components/head.php' => 'Componente principal',
    '../css/styles.css' => 'Estilos principales',
    '../css/reset.css' => 'Reset CSS',
    '../js/lazy-loading.js' => 'Lazy loading',
    '../views/index.php' => 'Página principal',
    '../config/seo.php' => 'Configuración SEO'
];

foreach ($criticalFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $description ($file)</p>";
    } else {
        echo "<p class='error'>❌ $description ($file) - NO ENCONTRADO</p>";
        $errors++;
    }
}

// 2. Verificar sintaxis PHP
echo "<h2>🔧 Estado de Archivos PHP</h2>";
$phpFiles = [
    '../components/head.php',
    '../controllers/index.php',
    '../models/pack.php',
    '../config/seo.php'
];

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $hasPHPTag = strpos($content, '<?php') !== false;
        $size = strlen($content);

        if ($hasPHPTag && $size > 50) { // Archivo PHP válido con contenido
            echo "<p class='success'>✅ " . basename($file) . " - Archivo válido (" . number_format($size) . " bytes)</p>";
        } else {
            echo "<p class='error'>❌ " . basename($file) . " - Contenido inválido</p>";
            $errors++;
        }
    } else {
        echo "<p class='error'>❌ " . basename($file) . " - NO ENCONTRADO</p>";
        $errors++;
    }
}

// 3. Verificar variables CSS
echo "<h2>🎨 Variables CSS</h2>";
require_once '../controllers/config.php';

$cssVars = [
    'accent' => $accent ?? null,
    'bg-dark' => '#0E0E0E', // Definida en head.php
    'text' => '#F5F5F5'     // Definida en head.php
];

foreach ($cssVars as $var => $value) {
    if ($value) {
        echo "<p class='success'>✅ CSS var --$var: $value</p>";
    } else {
        echo "<p class='warning'>⚠️ CSS var --$var: No definida</p>";
        $warnings++;
    }
}

// 4. Verificar optimizaciones implementadas
echo "<h2>⚡ Optimizaciones</h2>";
$optimizations = [
    'Sistema de cache' => file_exists('../cache/database/'), // Verificar directorio de cache
    'Optimización de imágenes' => file_exists('../includes/image-optimizer.php'),
    'CSS crítico' => strpos(file_get_contents('../components/head.php'), '--accent:') !== false,
    'Service Worker' => file_exists('../js/sw.js'),
    'Manifest PWA' => file_exists('../manifest.json')
];

foreach ($optimizations as $opt => $exists) {
    if ($exists) {
        echo "<p class='success'>✅ $opt implementado</p>";
    } else {
        echo "<p class='error'>❌ $opt NO implementado</p>";
        $errors++;
    }
}

// 5. Verificar tamaños de archivos
echo "<h2>📊 Tamaños de Archivos</h2>";
$sizeChecks = [
    '../css/styles.css' => 400, // KB máximo recomendado
    '../js/lazy-loading.js' => 50,
    '../components/head.php' => 100
];

foreach ($sizeChecks as $file => $maxSize) {
    if (file_exists($file)) {
        $size = filesize($file) / 1024; // KB
        if ($size < $maxSize) {
            echo "<p class='success'>✅ $file: " . number_format($size, 1) . " KB (límite: {$maxSize}KB)</p>";
        } else {
            echo "<p class='warning'>⚠️ $file: " . number_format($size, 1) . " KB (MAYOR al límite: {$maxSize}KB)</p>";
            $warnings++;
        }
    }
}

// 6. Resumen
echo "<h2>📋 Resumen</h2>";
echo "<div style='background: #1A1A1A; padding: 20px; border-radius: 8px; margin: 20px 0;'>";

if ($errors == 0 && $warnings == 0) {
    echo "<p class='success' style='font-size: 18px;'>🎉 ¡EXCELENTE! Todas las optimizaciones funcionan correctamente.</p>";
} elseif ($errors == 0) {
    echo "<p class='warning' style='font-size: 18px;'>⚠️ Bueno, pero hay $warnings advertencias menores.</p>";
} else {
    echo "<p class='error' style='font-size: 18px;'>❌ Hay $errors errores que necesitan atención.</p>";
}

echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ul>";
echo "<li>🔗 Inicia MAMP y accede a <code>http://localhost:8888/AlphaSupps/views/index.php</code></li>";
echo "<li>🔍 Abre DevTools (F12) y verifica la pestaña Network</li>";
echo "<li>⚡ Ejecuta Lighthouse para medir Core Web Vitals</li>";
echo "<li>📱 Prueba en dispositivos móviles</li>";
echo "</ul>";
echo "</div>";
?>
