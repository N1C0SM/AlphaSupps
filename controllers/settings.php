<?php
/**
 * =====================================================
 * 🧩 ALPHASUPPS THEME UTILITIES
 * -----------------------------------------------------
 * Genera variables de color dinámicas y un favicon SVG
 * personalizado basado en la configuración actual.
 * =====================================================
 */

require_once __DIR__ . '/../models/settings.php';


// ============================
// 🎯 FUNCIONES DE UTILIDAD
// ============================

/**
 * 🎨 Aclara un color HEX determinado.
 *
 * Aumenta la luminosidad de un color en formato HEX sumando un porcentaje
 * hacia el blanco (255,255,255). Útil para generar variantes más claras del color principal.
 *
 * @param string $color   Color en formato HEX (por ejemplo "#e0b94d" o "#fff").
 * @param float  $percent Porcentaje de aclarado (0.0 a 1.0). Por defecto 0.25 (25% más claro).
 *
 * @return string Color aclarado en formato HEX (por ejemplo "#f3d676").
 */
function lighten_color($color, $percent = 0.25) {
    $color = str_replace('#', '', $color);
    if (strlen($color) === 3) {
        $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
    }
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));

    $r = min(255, $r + (255 - $r) * $percent);
    $g = min(255, $g + (255 - $g) * $percent);
    $b = min(255, $b + (255 - $b) * $percent);

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}


/**
 * 🎨 Convierte un color HEX a valores RGB.
 *
 * @param string $hex Color en formato HEX (3 o 6 dígitos, con o sin "#").
 * @return array Array con tres valores: [R, G, B].
 */
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return [$r, $g, $b];
}


/**
 * 🎨 Ajusta un color (para bordes o sombras).
 *
 * Similar a lighten_color(), pero con un propósito decorativo o de contraste.
 *
 * @param string $hex      Color en formato HEX.
 * @param float  $percent  Porcentaje de ajuste hacia blanco (0.0 a 1.0). Por defecto 0.35.
 *
 * @return string Color ajustado en formato HEX.
 */
function adjust_color($hex, $percent = 0.35) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = min(255, $r + (255 - $r) * $percent);
    $g = min(255, $g + (255 - $g) * $percent);
    $b = min(255, $b + (255 - $b) * $percent);

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}


/**
 * 🎨 Convierte un nombre de color HTML (por ejemplo "red") a HEX.
 *
 * Si el color no se reconoce, devuelve dorado por defecto.
 *
 * @param string $name Nombre del color (por ejemplo "red", "white", "blue").
 * @return string Código HEX equivalente (por ejemplo "#ff0000").
 */
function colorNameToHex($name) {
    $colors = [
        'black' => '#000000',
        'white' => '#ffffff',
        'red' => '#ff0000',
        'green' => '#00ff00',
        'blue' => '#0000ff',
        'gold' => '#ffd700',
        'yellow' => '#ffff00',
        'purple' => '#800080',
        'orange' => '#ffa500',
        'silver' => '#c0c0c0'
    ];
    $name = strtolower(trim($name));
    return $colors[$name] ?? '#ffd700';
}


// ============================
// ⚙️ CONFIGURACIÓN Y DATOS
// ============================

$settings = getSettings();

// Verificar que hay configuración
if (!$settings) {
    // Usar valores por defecto si no hay configuración
    $settings = [
        'accent_color' => '#e0b94d',
        'site_name' => 'AlphaSupps'
    ];
}

// Color de acento dinámico (nombre o HEX)
if (isset($settings['accent_color'])) {
    $accentInput = $settings['accent_color'];
    $accent = preg_match('/^#[0-9A-Fa-f]{6}$/', $accentInput)
        ? $accentInput
        : colorNameToHex($accentInput);
} else {
    $accent = '#ffd700'; // Default dorado
}

$accent_light = lighten_color($accent, 0.25);
$gold = $accent;


// ============================
// 🖼️ FAVICON DINÁMICO
// ============================

$svgContent = "
<svg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 100 100'>
  <g fill='none' stroke='{$accent}' stroke-width='6' stroke-linecap='round' stroke-linejoin='round'>
    <circle cx='50' cy='50' r='46'/>
    <path d='M50 18 L72 75 L28 75 Z'/>
    <path d='M47 32 C49 38, 53 42, 51 47 C49 52, 43 56, 46 61 L56 72'/>
  </g>
</svg>
";

$dir = __DIR__ . '/../images/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Archivo favicon con color en el nombre
$colorName = preg_replace('/[^a-z0-9]/i', '', $settings['accent_color'] ?? 'gold');
$uniqueFavicon = "favicon_{$colorName}.svg";
$filePath = $dir . $uniqueFavicon;
file_put_contents($filePath, $svgContent);

// Limpieza: mantener máximo 5 favicons
$files = glob($dir . 'favicon_*.svg');
if (count($files) > 5) {
    foreach (array_slice($files, 0, -5) as $oldFile) {
        @unlink($oldFile);
    }
}


// ============================
// 💡 VARIABLES DINÁMICAS
// ============================

list($r, $g, $b) = hexToRgb($gold);

$shadow_gold = "0 8px 25px rgba($r, $g, $b, 0.15), 0 4px 14px rgba(0, 0, 0, 0.55)";
$shadow_gold_hover = "0 12px 35px rgba($r, $g, $b, 0.25), 0 6px 18px rgba(0, 0, 0, 0.6)";
$border_gold = adjust_color($gold, 0.35);


// ============================
// 📦 PASAR DATOS A LA VISTA
// ============================

$viewData = [
    'shadow_gold_hover' => $shadow_gold_hover,
    'shadow_gold' => $shadow_gold,
    'border_gold' => $border_gold,
    'accent' => $accent,
    'accent_light' => $accent_light,
    'gold' => $gold,
    'uniqueFavicon' => $uniqueFavicon,
    'settings' => $settings
];
?>