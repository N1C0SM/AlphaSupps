<?php
/**
 * Health Check - AlphaSupps
 * Verificación completa del estado del sistema (sin BD)
 */

echo "<h1>🏥 Health Check - AlphaSupps</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #0E0E0E; color: #F5F5F5; } .success { color: #10B981; } .error { color: #EF4444; } .warning { color: #F59E0B; } .health-card { background: #1A1A1A; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #e0b94d; }</style>";

$score = 0;
$maxScore = 100;

// 1. Verificar estructura de archivos
echo "<div class='health-card'>";
echo "<h2>📁 Estructura de Archivos</h2>";
$structureTests = [
    'components/' => ['head.php', 'header.php', 'footer.php'],
    'css/' => ['styles.css', 'reset.css', 'global-modern.css'],
    'js/' => ['lazy-loading.js', 'cart.js', 'nav.js'],
    'models/' => ['db.php', 'user.php', 'pack.php'],
    'controllers/' => ['config.php', 'index.php'],
    'views/' => ['index.php', 'supplements.php', 'packs.php'],
    'config/' => ['seo.php', 'session.php']
];

$structureScore = 0;
foreach ($structureTests as $dir => $files) {
    $dirExists = is_dir('../' . $dir);
    if ($dirExists) {
        $filesExist = 0;
        foreach ($files as $file) {
            if (file_exists('../' . $dir . $file)) $filesExist++;
        }
        $completion = round(($filesExist / count($files)) * 100);
        echo "<p class='success'>✅ $dir: $completion% completo ($filesExist/" . count($files) . " archivos)</p>";
        $structureScore += $completion;
    } else {
        echo "<p class='error'>❌ $dir: Directorio no encontrado</p>";
    }
}
$structureScore = round($structureScore / count($structureTests));
$score += $structureScore * 0.3; // 30% del score total
echo "<p><strong>Puntuación estructura: $structureScore/100</strong></p>";
echo "</div>";

// 2. Verificar sintaxis PHP
echo "<div class='health-card'>";
echo "<h2>🔧 Estado de Archivos PHP</h2>";
$syntaxFiles = [
    '../components/head.php',
    '../controllers/config.php',
    '../models/pack.php',
    '../config/seo.php'
];

$syntaxErrors = 0;
foreach ($syntaxFiles as $file) {
    if (file_exists($file)) {
        // Verificar que el archivo es legible y tiene contenido PHP válido
        $content = file_get_contents($file);
        $hasPHPTag = strpos($content, '<?php') !== false;
        $size = strlen($content);

        if ($hasPHPTag && $size > 100) { // Archivo con contenido PHP razonable
            echo "<p class='success'>✅ " . basename($file) . ": Archivo válido (" . number_format($size) . " bytes)</p>";
        } else {
            echo "<p class='error'>❌ " . basename($file) . ": Contenido sospechoso</p>";
            $syntaxErrors++;
        }
    } else {
        echo "<p class='error'>❌ " . basename($file) . ": Archivo no encontrado</p>";
        $syntaxErrors++;
    }
}

$syntaxScore = $syntaxErrors > 0 ? 0 : 100;
$score += $syntaxScore * 0.2; // 20% del score total
echo "<p><strong>Puntuación archivos: $syntaxScore/100</strong></p>";
echo "<p class='warning'><small>Nota: Verificación completa de sintaxis requiere entorno CLI</small></p>";
echo "</div>";

// 3. Verificar variables CSS críticas
echo "<div class='health-card'>";
echo "<h2>🎨 Variables CSS</h2>";
require_once '../controllers/config.php';

$cssVars = ['accent', 'accent_light', 'gold'];
$cssScore = 0;

foreach ($cssVars as $var) {
    if (isset($$var) && !empty($$var)) {
        echo "<p class='success'>✅ \$$var = " . $$var . "</p>";
        $cssScore += 33;
    } else {
        echo "<p class='error'>❌ \$$var no definida</p>";
    }
}

$score += round($cssScore * 0.2); // 20% del score total
echo "<p><strong>Puntuación CSS: $cssScore/100</strong></p>";
echo "</div>";

// 4. Verificar optimizaciones implementadas
echo "<div class='health-card'>";
echo "<h2>⚡ Optimizaciones</h2>";
$optimizations = [
    'CSS crítico en head.php' => strpos(file_get_contents('../components/head.php'), '--accent:') !== false,
    'Sistema PWA' => file_exists('../manifest.json') && file_exists('../sw.js'),
    'Optimización de imágenes' => file_exists('../includes/image-optimizer.php'),
    'SEO configurado' => file_exists('../config/seo.php')
];

$optScore = 0;
foreach ($optimizations as $opt => $status) {
    if ($status) {
        echo "<p class='success'>✅ $opt</p>";
        $optScore += 25;
    } else {
        echo "<p class='error'>❌ $opt</p>";
    }
}

$score += round($optScore * 0.3); // 30% del score total
echo "<p><strong>Puntuación optimizaciones: $optScore/100</strong></p>";
echo "</div>";

// 5. Resultado final
echo "<div class='health-card'>";
echo "<h2>📊 RESULTADO FINAL</h2>";
echo "<h3 style='font-size: 24px; margin: 10px 0;'>";

if ($score >= 90) {
    echo "<span class='success'>🎉 Puntuación: $score/100 - SISTEMA SALUDABLE</span>";
} elseif ($score >= 70) {
    echo "<span class='warning'>⚠️ Puntuación: $score/100 - REQUIERE ATENCIÓN</span>";
} else {
    echo "<span class='error'>❌ Puntuación: $score/100 - ACCIÓN REQUERIDA</span>";
}

echo "</h3>";

if ($score >= 90) {
    echo "<p>✅ Tu sitio AlphaSupps está completamente optimizado y listo para producción.</p>";
} elseif ($score >= 70) {
    echo "<p>⚠️ Hay algunos aspectos que necesitan atención, pero el sistema es funcional.</p>";
} else {
    echo "<p>❌ Se requieren correcciones importantes antes de la producción.</p>";
}

echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ul>";
echo "<li>🏥 Ejecuta este health check regularmente</li>";
echo "<li>⚡ Revisa speed-test.php para métricas detalladas</li>";
echo "<li>🔧 Soluciona cualquier problema identificado</li>";
echo "<li>🚀 Despliega cuando el score sea ≥90</li>";
echo "</ul>";
echo "</div>";
?>
