<?php
require_once __DIR__ . '/config/session.php';

function loadEnv($path)
{
    if (!file_exists($path))
        return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);

        $value = trim($value, "\"'");

        putenv("$key=$value");
    }
}

loadEnv(__DIR__ . '/.env');

define("DB_HOST", getenv('DB_HOST') ?: 'localhost');
define("DB_USER", getenv('DB_USER') ?: 'root');
define("DB_PASS", getenv('DB_PASS') ?: '');
define("DB_NAME", getenv('DB_NAME') ?: 'alphasupps');
define("DB_PORT", getenv('DB_PORT') ?: 3306);
$defaultSocket = file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')
    ? '/Applications/MAMP/tmp/mysql/mysql.sock'
    : '';
define("DB_SOCKET", getenv('DB_SOCKET') ?: $defaultSocket);

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME,
    (int)DB_PORT,
    DB_SOCKET ?: null
);
if ($conn->connect_errno) {
    die("Error de conexión MySQL: " . $conn->connect_error);
}

$mode = strtolower(trim((string)getenv('DEFAULT_STRIPE_MODE')));
if ($mode === '') {
    $mode = 'test';
}

$query = $conn->query("SELECT stripe_mode FROM settings WHERE id = 1 LIMIT 1");
if ($query && $row = $query->fetch_assoc()) {
    $dbMode = strtolower(trim((string)$row['stripe_mode']));
    if ($dbMode !== '') {
        $mode = $dbMode;
    }
}

$mode = ($mode === 'live') ? 'live' : 'test';

define('STRIPE_PUBLIC_KEY', getenv('STRIPE_PUBLIC_KEY_' . strtoupper($mode)) ?: 'pk_test_default');
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY_' . strtoupper($mode)) ?: 'sk_test_default');

/**
 * -----------------------------------------------------------
 *  SMTP
 * -----------------------------------------------------------
 */
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', getenv('SMTP_USER') ?: 'your_email@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'your_password');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);


/**
 * -----------------------------------------------------------
 *  EMISOR / EMPRESA
 * -----------------------------------------------------------
 */
define('USER_WEBSITE', getenv('USER_WEBSITE') ?: 'https://alphasupps.alwaysdata.net/views/');

define('EMISOR_NAME', getenv('EMISOR_NAME') ?: 'AlphaSupps');
define('EMISOR_NIF', getenv('EMISOR_NIF') ?: 'B12345678');
define('EMISOR_ADDRESS', getenv('EMISOR_ADDRESS') ?: 'Calle Ficticia 123, Madrid');
define('EMISOR_PHONE', getenv('EMISOR_PHONE') ?: '+34 600 123 456');
define('EMISOR_EMAIL', getenv('EMISOR_EMAIL') ?: 'info@alphasupps.com');

define('DEFAULT_SITE_NAME', getenv('DEFAULT_SITE_NAME') ?: 'AlphaSupps');
define('DEFAULT_CONTACT_EMAIL', getenv('DEFAULT_CONTACT_EMAIL') ?: 'contact@alphasupps.com');
define('DEFAULT_ACCENT_COLOR', getenv('DEFAULT_ACCENT_COLOR') ?: '#e0b94d');
define('DEFAULT_STRIPE_MODE', getenv('DEFAULT_STRIPE_MODE') ?: 'test');
define('DEFAULT_LOGO_URL', getenv('DEFAULT_LOGO_URL') ?: '/images/logo.png');
define('DEFAULT_META_DESCRIPTION', getenv('DEFAULT_META_DESCRIPTION') ?: 'Tienda de suplementos premium con calidad y ciencia.');

date_default_timezone_set("Europe/Madrid");
ini_set("default_charset", "UTF-8");

/**
 * -----------------------------------------------------------
 *  RUTA BASE DEL PROYECTO
 * -----------------------------------------------------------
 */
// Detectar automáticamente la ruta base del proyecto
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$projectRoot = dirname($scriptPath);
if ($projectRoot === '/' || $projectRoot === '\\') {
    $projectRoot = '';
}
define('BASE_URL', $projectRoot . '/');

if (getenv('APP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
} else {
    error_reporting(0);
}
