<?php
// Script de debug para el sistema de reviews
echo "<h1>🔍 Debug del Sistema de Reviews</h1>\n\n";

echo "<h2>1. Verificando archivos requeridos...</h2>\n<ul>\n";

// Verificar archivos
$files_to_check = [
    'api/rate-product.php' => 'API principal de ratings',
    'models/reviews.php' => 'Modelo de reviews',
    'models/pack.php' => 'Modelo de packs',
    'models/supplement.php' => 'Modelo de supplements',
    'views/pack.php' => 'Vista de packs',
    'views/supplement.php' => 'Vista de supplements',
    'css/styles.css' => 'Estilos CSS'
];

foreach ($files_to_check as $file => $description) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "<li style='color: green;'>✅ $description: $file</li>\n";
    } else {
        echo "<li style='color: red;'>❌ $description: $file (NO ENCONTRADO)</li>\n";
    }
}
echo "</ul>\n\n";

echo "<h2>2. Verificando funciones requeridas...</h2>\n<ul>\n";

// Verificar funciones en reviews.php
$functions_to_check = [
    'getReviewsByPack' => 'Obtener reviews de packs',
    'getReviewsBySuplement' => 'Obtener reviews de supplements',
    'addPackReview' => 'Agregar review a pack',
    'addReview' => 'Agregar review a supplement',
    'getUserReviewForPack' => 'Verificar review de usuario en pack',
    'getUserReviewForSupplement' => 'Verificar review de usuario en supplement'
];

require_once 'models/reviews.php';

foreach ($functions_to_check as $function => $description) {
    if (function_exists($function)) {
        echo "<li style='color: green;'>✅ $description: $function()</li>\n";
    } else {
        echo "<li style='color: red;'>❌ $description: $function() (NO EXISTE)</li>\n";
    }
}
echo "</ul>\n\n";

echo "<h2>3. Verificando conexión a base de datos...</h2>\n";

try {
    require_once 'models/db.php';
    $conn = connectToDatabase();
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>\n";

    // Verificar tablas
    $tables = ['packs', 'supplements', 'reviews', 'users'];
    echo "<h3>Tablas existentes:</h3>\n<ul>\n";

    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<li style='color: green;'>✅ Tabla '$table' existe</li>\n";
        } else {
            echo "<li style='color: red;'>❌ Tabla '$table' NO existe</li>\n";
        }
    }
    echo "</ul>\n";

    // Verificar columnas en tabla reviews
    echo "<h3>Columnas en tabla 'reviews':</h3>\n<ul>\n";
    $result = $conn->query("DESCRIBE reviews");
    if ($result) {
        $expected_columns = ['id', 'coment', 'author', 'idSuplement', 'rating', 'idPack', 'user_id', 'created_at'];
        $existing_columns = [];

        while ($row = $result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }

        foreach ($expected_columns as $col) {
            if (in_array($col, $existing_columns)) {
                echo "<li style='color: green;'>✅ Columna '$col' existe</li>\n";
            } else {
                echo "<li style='color: red;'>❌ Columna '$col' FALTA</li>\n";
            }
        }
    } else {
        echo "<li style='color: red;'>❌ No se pudo consultar la tabla reviews</li>\n";
    }
    echo "</ul>\n";

    $conn->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>\n";
}

echo "<br>\n<hr>\n";
echo "<h2>🚀 Próximos pasos:</h2>\n";
echo "<ol>\n";
echo "<li><a href='update_database.php'>Ejecutar actualización de base de datos</a></li>\n";
echo "<li>Eliminar el archivo update_database.php después de ejecutarlo</li>\n";
echo "<li>Probar el sistema de reviews en una página de pack o supplement</li>\n";
echo "<li>Eliminar este archivo de debug por seguridad</li>\n";
echo "</ol>\n";

echo "<p><strong>Nota:</strong> Si sigues teniendo problemas, verifica que MAMP esté ejecutándose y que la base de datos esté accesible.</p>\n";
?>





