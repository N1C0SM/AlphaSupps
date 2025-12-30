<?php
require_once '../models/settings.php';
$title = "AlphaSupps | Configuración General";
$description = "Gestiona los ajustes globales del sitio AlphaSupps, como nombre, correo o preferencias visuales.";
$url = "https://alphasupps.alwaysdata.net/admin/settings.php";
require_once '../models/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['reset_defaults'])) {
        if (resetSettingsToDefault()) {
            header("Location: ../admin/settings.php?reset=success");
            exit;
        } else {
            header("Location: ../admin/settings.php?reset=error");
            exit;
        }
    }

    $site_name = trim($_POST['site_name']);
    $contact_email = trim($_POST['contact_email']);
    $accent_color = trim($_POST['accent_color']);
    $stripe_mode = trim($_POST['stripe_mode']);

    if (updateSettings($site_name, $contact_email, $accent_color, $stripe_mode)) {
        header("Location: ../admin/settings.php?update=success");
    } else {
        header("Location: ../admin/settings.php?update=error");
    }
    exit;
}
?>
?>
