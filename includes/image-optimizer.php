<?php
/**
 * Optimizador de Imágenes Automático - AlphaSupps
 * Convierte imágenes a WebP y optimiza tamaños
 */

class ImageOptimizer {
    private $quality;
    private $maxWidth;
    private $maxHeight;

    public function __construct($quality = 85, $maxWidth = 1200, $maxHeight = 1200) {
        $this->quality = $quality;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * Optimiza una imagen y crea versiones WebP
     */
    public function optimizeImage($sourcePath, $destPath = null) {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $destPath = $destPath ?? $sourcePath;

        // Obtener información de la imagen
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Crear imagen desde el archivo fuente
        $image = null;
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        // Redimensionar si es necesario
        $image = $this->resizeImage($image, $width, $height);

        // Optimizar y guardar
        $optimized = $this->saveOptimizedImage($image, $destPath, $mime);

        // Crear versión WebP
        $webpPath = $this->getWebpPath($destPath);
        $this->saveWebpImage($image, $webpPath);

        imagedestroy($image);

        return $optimized;
    }

    /**
     * Redimensiona imagen manteniendo proporción
     */
    private function resizeImage($image, $width, $height) {
        if ($width <= $this->maxWidth && $height <= $this->maxHeight) {
            return $image;
        }

        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);

        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagedestroy($image);
        return $resizedImage;
    }

    /**
     * Guarda imagen optimizada
     */
    private function saveOptimizedImage($image, $path, $mime) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        switch ($mime) {
            case 'image/jpeg':
                return imagejpeg($image, $path, $this->quality);
            case 'image/png':
                // Para PNG, reducir calidad significa más compresión
                return imagepng($image, $path, 9);
            case 'image/gif':
                return imagegif($image, $path);
        }

        return false;
    }

    /**
     * Guarda versión WebP
     */
    private function saveWebpImage($image, $path) {
        if (function_exists('imagewebp')) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            return imagewebp($image, $path, $this->quality);
        }
        return false;
    }

    /**
     * Obtiene la ruta WebP correspondiente
     */
    private function getWebpPath($originalPath) {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
    }

    /**
     * Genera HTML con soporte WebP
     */
    public static function getResponsiveImageHtml($src, $alt, $class = '', $sizes = null) {
        $pathInfo = pathinfo($src);
        $webpSrc = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

        $html = '<picture>';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $webpSrc)) {
            $html .= '<source srcset="' . $webpSrc . '" type="image/webp">';
        }
        $html .= '<img src="' . $src . '" alt="' . htmlspecialchars($alt) . '"';
        if ($class) {
            $html .= ' class="' . $class . '"';
        }
        if ($sizes) {
            $html .= ' sizes="' . $sizes . '"';
        }
        $html .= ' loading="lazy">';
        $html .= '</picture>';

        return $html;
    }

    /**
     * Procesa lote de imágenes
     */
    public function optimizeDirectory($directory, $recursive = false) {
        $images = $this->findImages($directory, $recursive);
        $optimized = 0;

        foreach ($images as $image) {
            if ($this->optimizeImage($image)) {
                $optimized++;
            }
        }

        return $optimized;
    }

    /**
     * Encuentra imágenes en directorio
     */
    private function findImages($directory, $recursive = false) {
        $images = [];
        $iterator = $recursive ?
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) :
            new DirectoryIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $file->getPathname();
                }
            }
        }

        return $images;
    }
}

// Funciones helper
function optimizeImage($src, $dest = null) {
    $optimizer = new ImageOptimizer();
    return $optimizer->optimizeImage($src, $dest);
}

function getResponsiveImage($src, $alt, $class = '', $sizes = null) {
    return ImageOptimizer::getResponsiveImageHtml($src, $alt, $class, $sizes);
}

// Optimización automática al subir imágenes (hook para futuro)
function autoOptimizeUploadedImage($uploadedFile) {
    $optimizer = new ImageOptimizer();
    $targetPath = 'images/uploads/' . basename($uploadedFile['name']);
    move_uploaded_file($uploadedFile['tmp_name'], $targetPath);
    $optimizer->optimizeImage($targetPath);
    return $targetPath;
}
?>
