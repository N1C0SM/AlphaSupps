<?php
/**
 * Sistema de Cache Inteligente - AlphaSupps
 * Acelera la carga de datos frecuentemente accedidos
 */

class CacheManager {
    private $cacheDir;
    private $cacheTime;

    public function __construct($cacheTime = 3600) { // 1 hora por defecto
        $this->cacheDir = __DIR__ . '/files/';
        $this->cacheTime = $cacheTime;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Obtiene datos del cache o ejecuta callback si no existe
     */
    public function get($key, $callback, $ttl = null) {
        $cacheFile = $this->getCacheFile($key);
        $ttl = $ttl ?? $this->cacheTime;

        // Verificar si el cache existe y no ha expirado
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $data = unserialize(file_get_contents($cacheFile));
            return $data;
        }

        // Ejecutar callback y cachear resultado
        $data = $callback();
        $this->set($key, $data);
        return $data;
    }

    /**
     * Guarda datos en cache
     */
    public function set($key, $data) {
        $cacheFile = $this->getCacheFile($key);
        file_put_contents($cacheFile, serialize($data));
    }

    /**
     * Elimina cache específico
     */
    public function delete($key) {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Limpia todo el cache
     */
    public function clear() {
        $files = glob($this->cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Obtiene la ruta del archivo de cache
     */
    private function getCacheFile($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }

    /**
     * Obtiene estadísticas del cache
     */
    public function getStats() {
        $files = glob($this->cacheDir . '*');
        $totalSize = 0;
        $fileCount = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
                $fileCount++;
            }
        }

        return [
            'files' => $fileCount,
            'size' => $totalSize,
            'size_human' => $this->formatBytes($totalSize)
        ];
    }

    /**
     * Formatea bytes en formato legible
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// Instancia global del cache manager
$cache = new CacheManager();

// Funciones helper para cachear consultas comunes
function cacheSupplements($limit = null, $offset = null) {
    global $cache;
    $key = 'supplements_' . ($limit ?? 'all') . '_' . ($offset ?? '0');

    return $cache->get($key, function() use ($limit, $offset) {
        require_once __DIR__ . '/../models/supplement.php';
        return getAllSupplements($limit, $offset, false);
    }, 1800); // Cache 30 minutos para productos
}

function cachePacks($limit = null) {
    global $cache;
    $key = 'packs_' . ($limit ?? 'all');

    return $cache->get($key, function() use ($limit) {
        require_once __DIR__ . '/../models/pack.php';
        require_once __DIR__ . '/../models/db.php';
        $conn = connectToDatabase();
        return getPublicPacks($conn);
    }, 1800); // Cache 30 minutos para packs
}

function cacheBrands() {
    global $cache;

    return $cache->get('brands', function() {
        require_once __DIR__ . '/../models/brand.php';
        return getBrands();
    }, 3600); // Cache 1 hora para marcas (cambian menos)
}

// Función para limpiar cache cuando se actualizan datos
function clearCacheOnUpdate($type) {
    global $cache;
    switch ($type) {
        case 'supplement':
            // Las claves se hashean, así que limpiamos todo para evitar datos obsoletos
            if (isset($cache)) {
                $cache->clear();
            }
            break;
        case 'pack':
            $cache->delete('packs_all');
            break;
        case 'brand':
            $cache->delete('brands');
            break;
        case 'all':
            $cache->clear();
            break;
    }
}

// Función para pre-cargar datos críticos en background
function preloadCriticalCache() {
    // Pre-cargar datos críticos al inicio
    cacheBrands();
    cacheSupplements(12); // Primer página de productos
    cachePacks();
}

// Auto-preload si es la primera carga
if (!file_exists(__DIR__ . '/files/preloaded.lock')) {
    preloadCriticalCache();
    file_put_contents(__DIR__ . '/files/preloaded.lock', time());
}
?>
