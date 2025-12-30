<?php
/**
 * Diagnóstico de Variables CSS - AlphaSupps
 * Verifica que todas las variables CSS críticas estén definidas
 */

echo "<h1>🔍 Diagnóstico de Variables CSS</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #0E0E0E; color: #F5F5F5; } .success { color: #10B981; } .error { color: #EF4444; } .warning { color: #F59E0B; } .var-list { background: #1A1A1A; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #e0b94d; }</style>";

// Simular la carga del controlador de configuración
echo "<h2>📋 Verificación de Controlador Config</h2>";
require_once '../controllers/config.php';

$cssVars = [
    // Variables principales
    'accent' => $accent ?? 'NO DEFINIDA',
    'accent_light' => $accent_light ?? 'NO DEFINIDA',
    'gold' => $gold ?? 'NO DEFINIDA',
    'shadow_gold' => $shadow_gold ?? 'NO DEFINIDA',
    'shadow_gold_hover' => $shadow_gold_hover ?? 'NO DEFINIDA',

    // Variables de configuración adicionales
    'gold_hover' => $gold_hover ?? 'NO DEFINIDA',
];

echo "<div class='var-list'>";
echo "<h3>Variables PHP disponibles:</h3>";
foreach ($cssVars as $var => $value) {
    $status = ($value === 'NO DEFINIDA') ? 'error' : 'success';
    echo "<p class='$status'>💰 \$$var = $value</p>";
}
echo "</div>";

// Verificar archivos CSS que usan variables
echo "<h2>📄 Archivos CSS que usan variables</h2>";

$cssFiles = [
    '../css/reset.css' => ['--font-primary', '--text-primary', '--bg-primary', '--primary-gold'],
    '../css/styles.css' => ['--bg-dark', '--text', '--accent', '--bg-panel', '--shadow'],
    '../css/global-modern.css' => ['--bg-primary', '--text-primary', '--primary-gold'],
    '../css/buttons.css' => ['--accent', '--gold', '--shadow'],
    '../css/cards.css' => ['--bg-panel', '--shadow', '--gold'],
];

foreach ($cssFiles as $file => $vars) {
    echo "<div class='var-list'>";
    echo "<h3>$file</h3>";
    foreach ($vars as $var) {
        $defined = strpos(file_get_contents($file), $var) !== false;
        $status = $defined ? 'success' : 'warning';
        echo "<p class='$status'>" . ($defined ? '✅' : '⚠️') . " Usa $var</p>";
    }
    echo "</div>";
}

// Verificar que las variables se definan en head.php
echo "<h2>🎯 Variables definidas en head.php</h2>";
$headContent = file_get_contents('../components/head.php');
$headVars = [
    '--accent:', '--bg-dark:', '--text:', '--font-body:', '--shadow:',
    '--bg-panel:', '--text-light:', '--primary-gold:', '--shadow-gold:'
];

echo "<div class='var-list'>";
echo "<h3>Definiciones encontradas:</h3>";
foreach ($headVars as $var) {
    $defined = strpos($headContent, $var) !== false;
    $status = $defined ? 'success' : 'error';
    echo "<p class='$status'>" . ($defined ? '✅' : '❌') . " $var encontrada en head.php</p>";
}
echo "</div>";

// Recomendaciones
echo "<h2>💡 Solución para Variables CSS</h2>";
echo "<div class='var-list'>";
echo "<p><strong>Si las variables no se cargan:</strong></p>";
echo "<ul>";
echo "<li>✅ Asegúrate de que <code>controllers/config.php</code> se carga antes de head.php</li>";
echo "<li>✅ Verifica que las variables PHP estén definidas en config.php</li>";
echo "<li>✅ Confirma que head.php puede acceder a las variables PHP</li>";
echo "<li>✅ Prueba recargar la página con Ctrl+F5 (hard refresh)</li>";
echo "</ul>";
echo "</div>";

echo "<p style='margin-top: 30px; background: #1A1A1A; padding: 20px; border-radius: 8px;'>";
echo "🔄 <strong>Después de verificar:</strong> Ejecuta nuevamente <code>speed-test.php</code> para confirmar que todas las variables CSS estén definidas.";
echo "</p>";
?>
