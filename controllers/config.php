<?php

$user = $_SESSION['user'] ?? null;
$admin = $user && $user['role'] === 'admin';

// Cargar variables de entorno
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, ';') === 0) {
                continue;
            }

            if (strpos($line, 'export ') === 0) {
                $line = trim(substr($line, strlen('export ')));
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($key === '') {
                continue;
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// ============================================
// CONFIGURACIÓN DE COLORES Y TEMA - AlphaSupps
// ============================================

// Colores principales - siempre definidos con valores por defecto
$accent = '#e0b94d';              // Dorado fuerte
$accent_light = '#f0c75e';        // Dorado claro
$gold = '#e0b94d';                // Alias para accent
$gold_hover = '#e3c46f';          // Dorado hover

// Sombras para efectos visuales
$shadow_gold = '0 8px 25px rgba(224, 180, 77, 0.3)';
$shadow_gold_hover = '0 12px 35px rgba(224, 180, 77, 0.4)';

// Intentar cargar desde variables de entorno si existen
if (getenv('DEFAULT_ACCENT_COLOR')) {
    $accent = getenv('DEFAULT_ACCENT_COLOR');
    $gold = $accent;
}

// ============================================
// FIN CONFIGURACIÓN
// ============================================
