<?php
require_once __DIR__ . '/db.php';

/**
 * Obtiene los valores por defecto de configuración.
 *
 * @return array Array con los valores por defecto
 */
function getDefaultSettings() {
    $site_name = DEFAULT_SITE_NAME;
    $contact_email = DEFAULT_CONTACT_EMAIL;
    $accent_color = DEFAULT_ACCENT_COLOR;
    $stripe_mode = DEFAULT_STRIPE_MODE;
    $logo_url = DEFAULT_LOGO_URL;
    $meta_description = DEFAULT_META_DESCRIPTION;

    return [
        'site_name' => $site_name,
        'contact_email' => $contact_email,
        'accent_color' => $accent_color,
        'stripe_mode' => $stripe_mode,
        'logo_url' => $logo_url,
        'meta_description' => $meta_description
    ];
}

/**
 * Obtiene la configuración global del sitio.
 *
 * @return array|null Configuración actual del sitio o null si no existe
 */
function getSettings() {
    $conn = connectToDatabase();

    if (!$conn) {
        die("Error de conexión a la base de datos: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM settings LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row;
    }

    // Si no hay datos, devolver null en lugar de valores por defecto
    // Esto permite detectar cuando falta la configuración inicial
    return null;
}
/**
 * Actualiza los ajustes globales del sitio.
 *
 * @param string $site_name
 * @param string $contact_email
 * @param string $accent_color
 * @param string $stripe_mode
 * @return bool True si la actualización fue exitosa.
 */
function updateSettings($site_name, $contact_email, $accent_color, $stripe_mode) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("
        UPDATE settings
        SET site_name = ?, contact_email = ?, accent_color = ?, stripe_mode = ?, updated_at = NOW()
        WHERE id = 1
    ");
    $stmt->bind_param("ssss", $site_name, $contact_email, $accent_color, $stripe_mode);

    $ok = $stmt->execute();

    $stmt->close();
    return $ok;
}

/**
 * 🔄 Restaura los valores por defecto del sistema
 */
function resetSettingsToDefault() {
    $conn = connectToDatabase();

    $defaultSettings = getDefaultSettings();

    $stmt = $conn->prepare("UPDATE settings SET site_name = ?, contact_email = ?, accent_color = ?, stripe_mode = ? WHERE id = 1");
    $stmt->bind_param("ssss",
        $defaultSettings['site_name'],
        $defaultSettings['contact_email'],
        $defaultSettings['accent_color'],
        $defaultSettings['stripe_mode']
    );
    return $stmt->execute();
}

?>
