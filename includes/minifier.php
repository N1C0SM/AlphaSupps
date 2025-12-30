<?php
/**
 * Minificador de CSS y JS - AlphaSupps
 * Reduce el tamaño de archivos para carga más rápida
 */

class AssetMinifier {
    private $cacheDir;

    public function __construct() {
        $this->cacheDir = __DIR__ . '/../cache/minified/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Minifica CSS
     */
    public function minifyCSS($css) {
        // Remover comentarios
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remover espacios en blanco
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,>+])\s*/', '$1', $css);

        // Remover espacios alrededor de selectores
        $css = preg_replace('/\s*>\s*/', '>', $css);
        $css = preg_replace('/\s*\+\s*/', '+', $css);

        return trim($css);
    }

    /**
     * Minifica JavaScript
     */
    public function minifyJS($js) {
        // Remover comentarios de una línea
        $js = preg_replace('/\/\/.*$/m', '', $js);

        // Remover comentarios multilinea
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);

        // Remover espacios en blanco extras
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/\s*([{}:;,=()+\-*\/%&|!><])\s*/', '$1', $js);

        // Remover espacios alrededor de operadores
        $js = preg_replace('/\s*([=<>!]=|[<>])\s*/', '$1', $js);

        return trim($js);
    }

    /**
     * Combina múltiples archivos CSS
     */
    public function combineCSS($files) {
        $combined = '';
        foreach ($files as $file) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path)) {
                $combined .= file_get_contents($path) . "\n";
            }
        }
        return $this->minifyCSS($combined);
    }

    /**
     * Combina múltiples archivos JS
     */
    public function combineJS($files) {
        $combined = '';
        foreach ($files as $file) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path)) {
                $combined .= file_get_contents($path) . ";";
            }
        }
        return $this->minifyJS($combined);
    }

    /**
     * Obtiene archivo minificado con cache
     */
    public function getMinifiedFile($type, $files, $cacheKey = null) {
        $cacheKey = $cacheKey ?? md5(implode('', $files) . $type);
        $cacheFile = $this->cacheDir . $cacheKey . '.' . $type;

        if (file_exists($cacheFile) && $this->isCacheValid($files, $cacheFile)) {
            return file_get_contents($cacheFile);
        }

        // Generar archivo minificado
        $content = ($type === 'css') ? $this->combineCSS($files) : $this->combineJS($files);
        file_put_contents($cacheFile, $content);

        return $content;
    }

    /**
     * Verifica si el cache es válido
     */
    private function isCacheValid($files, $cacheFile) {
        $cacheTime = filemtime($cacheFile);

        foreach ($files as $file) {
            $path = __DIR__ . '/../' . $file;
            if (file_exists($path) && filemtime($path) > $cacheTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Limpia cache de archivos minificados
     */
    public function clearCache() {
        $files = glob($this->cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// Instancia global
$minifier = new AssetMinifier();

// Funciones helper para templates
function getMinifiedCSS($files) {
    global $minifier;
    return $minifier->getMinifiedFile('css', $files);
}

function getMinifiedJS($files) {
    global $minifier;
    return $minifier->getMinifiedFile('js', $files);
}

// Output directo para endpoints
function outputMinifiedCSS($files) {
    header('Content-Type: text/css');
    header('Cache-Control: public, max-age=31536000');
    echo getMinifiedCSS($files);
}

function outputMinifiedJS($files) {
    header('Content-Type: application/javascript');
    header('Cache-Control: public, max-age=31536000');
    echo getMinifiedJS($files);
}
?>
