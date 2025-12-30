<?php
/**
 * Prueba de Funciones Core - AlphaSupps
 * Verifica que las funcionalidades principales funcionen
 */

echo "<h1>🔧 Pruebas de Funciones Core</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; background: #0E0E0E; color: #F5F5F5; } .success { color: #10B981; } .error { color: #EF4444; } .warning { color: #F59E0B; } .test-section { background: #1A1A1A; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #e0b94d; }</style>";

function testSection($title, $callback) {
    echo "<div class='test-section'>";
    echo "<h3>$title</h3>";
    try {
        $callback();
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
}

// 1. Probar conexión a BD y consultas básicas
testSection("🗄️ Base de Datos", function() {
    global $dbConnection; // Conexión compartida para todos los tests

    try {
        require_once '../models/db.php';
        $dbConnection = connectToDatabase();

        if ($dbConnection) {
            // Probar consulta simple
            $result = $dbConnection->query("SELECT 1 as test");
            if ($result && $result->fetch_assoc()['test'] == 1) {
                echo "<p class='success'>✅ Conexión y consulta básica funcionan</p>";
                $dbWorking = true;
            } else {
                echo "<p class='error'>❌ Error en consulta básica: " . $dbConnection->error . "</p>";
                $dbWorking = false;
            }
        } else {
            echo "<p class='error'>❌ No se pudo conectar a la base de datos</p>";
            $dbWorking = false;
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
        $dbWorking = false;
    }

    // Guardar estado de BD para otros tests
    $GLOBALS['dbWorking'] = $dbWorking ?? false;
});

// 2. Probar modelos principales
testSection("📦 Modelos de Datos", function() {
    global $dbConnection;

    // Solo ejecutar si la BD está funcionando
    if (!($GLOBALS['dbWorking'] ?? false) || !$dbConnection) {
        echo "<p class='warning'>⚠️ Saltando test de modelos - Base de datos no disponible</p>";
        return;
    }

    try {
        // Usar la conexión compartida
        if ($dbConnection) {
            // Probar obtener packs públicos
            require_once '../models/pack.php';
            $packs = getPublicPacks($dbConnection);

            if (is_array($packs)) {
                echo "<p class='success'>✅ getPublicPacks() funciona (" . count($packs) . " packs encontrados)</p>";
            } else {
                echo "<p class='error'>❌ Error en getPublicPacks() - Resultado no es array</p>";
            }
        } else {
            echo "<p class='error'>❌ Conexión no disponible para modelos</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error en modelos de datos: " . $e->getMessage() . "</p>";
    }
    // No cerrar la conexión aquí - se cerrará al final del script
});

// Cerrar conexión al final del script
register_shutdown_function(function() {
    global $dbConnection;
    if (isset($dbConnection) && $dbConnection) {
        $dbConnection->close();
    }
});

// 3. Probar configuración
testSection("⚙️ Configuración del Sistema", function() {
    require_once '../controllers/config.php';

    $requiredVars = ['accent', 'accent_light', 'gold'];
    foreach ($requiredVars as $var) {
        if (isset($$var)) {
            echo "<p class='success'>✅ Variable \$$var definida: " . $$var . "</p>";
        } else {
            echo "<p class='error'>❌ Variable \$$var no definida</p>";
        }
    }
});

// 4. Probar SEO y páginas
testSection("🔍 SEO y Páginas", function() {
    require_once '../config/seo.php';

    if (isset($pages) && isset($pages['views']['index'])) {
        echo "<p class='success'>✅ Configuración SEO cargada</p>";
        echo "<p>Título: " . htmlspecialchars($pages['views']['index']['title']) . "</p>";
    } else {
        echo "<p class='error'>❌ Error en configuración SEO</p>";
    }
});

// 5. Probar sistema de archivos
testSection("📁 Sistema de Archivos", function() {
    $criticalFiles = [
        '../components/head.php',
        '../css/styles.css',
        '../js/lazy-loading.js',
        '../views/index.php'
    ];

    foreach ($criticalFiles as $file) {
        if (file_exists($file)) {
            echo "<p class='success'>✅ $file existe</p>";
        } else {
            echo "<p class='error'>❌ $file no encontrado</p>";
        }
    }
});

// 6. Probar sistema de cache
testSection("💾 Sistema de Cache", function() {
    // Verificar si existe el directorio de cache
    if (file_exists('../cache/database/')) {
        $cacheFiles = glob('../cache/database/*.cache');
        $cacheCount = count($cacheFiles);
        echo "<p class='success'>✅ Sistema de cache habilitado ($cacheCount archivos en cache)</p>";
    } else {
        echo "<p class='warning'>⚠️ Sistema de cache no inicializado (se creará automáticamente)</p>";
    }
});

// 7. Probar optimización de imágenes
testSection("🖼️ Optimización de Imágenes", function() {
    $imageOptimizerFile = '../includes/image-optimizer.php';

    if (file_exists($imageOptimizerFile)) {
        $content = file_get_contents($imageOptimizerFile);
        $hasClass = strpos($content, 'class ImageOptimizer') !== false;
        $hasFunctions = strpos($content, 'function getResponsiveImage') !== false;

        if ($hasClass && $hasFunctions) {
            echo "<p class='success'>✅ Sistema de optimización de imágenes disponible</p>";
        } else {
            echo "<p class='error'>❌ Archivo image-optimizer.php incompleto</p>";
        }
    } else {
        echo "<p class='error'>❌ Sistema de optimización de imágenes no encontrado</p>";
    }
});

// 8. Verificar sintaxis de archivos críticos
testSection("🔧 Estado de Archivos PHP", function() {
    $criticalFiles = [
        '../controllers/index.php',
        '../controllers/pack.php',
        '../models/user.php'
    ];

    foreach ($criticalFiles as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $hasPHPTag = strpos($content, '<?php') !== false;
            $size = strlen($content);

            if ($hasPHPTag && $size > 50) { // Archivo PHP válido con contenido
                echo "<p class='success'>✅ " . basename($file) . ": Archivo válido (" . number_format($size) . " bytes)</p>";
            } else {
                echo "<p class='error'>❌ " . basename($file) . ": Contenido inválido</p>";
            }
        } else {
            echo "<p class='warning'>⚠️ " . basename($file) . ": Archivo no encontrado</p>";
        }
    }

    echo "<p class='warning'><small>Nota: Verificación completa de sintaxis requiere entorno CLI</small></p>";
});

echo "<div class='test-section'>";
echo "<h3>📋 Próximos Pasos</h3>";
echo "<p>Si todas las pruebas pasan, el sitio está listo para producción:</p>";
echo "<ul>";
echo "<li>🔗 Accede a <code>http://localhost/test-functionality.php</code> para ver todas las pruebas</li>";
echo "<li>⚡ Accede a <code>http://localhost/speed-test.php</code> para métricas de rendimiento</li>";
echo "<li>🌐 Visita <code>http://localhost/views/index.php</code> para probar el sitio completo</li>";
echo "<li>🔍 Usa Lighthouse en DevTools para auditar el rendimiento</li>";
echo "</ul>";
echo "</div>";
?>
