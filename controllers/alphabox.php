<?php
session_start();

require_once '../models/alphabox.php';
require_once '../models/order.php'; // para sendMail
require_once '../config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;
if (!$action) exit("No se especificó ninguna acción.");

switch ($action) {
    case 'create':
        handleCreateAlphaBox();
        break;
    case 'confirm':
        handleConfirmAlphaBox();
        break;
    default:
        exit("Acción no válida.");
}

function handleCreateAlphaBox(): void {
    header('Content-Type: application/json');

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan  = strtolower(trim($_POST['plan'] ?? 'basica'));
    $freq  = strtolower(trim($_POST['frequency'] ?? 'mensual'));
    $notes = trim($_POST['notes'] ?? '');
    $userId = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? intval($_POST['user_id']) : null;

    $allowedPlans = ['basica', 'pro', 'elite'];
    $allowedFreq  = ['mensual', 'cada_2_meses', 'cada_3_meses'];
    if (!in_array($plan, $allowedPlans, true)) $plan = 'basica';
    if (!in_array($freq, $allowedFreq, true)) $freq = 'mensual';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "success" => false,
            "message" => "Completa nombre y un email válido."
        ]);
        return;
    }

    $token = bin2hex(random_bytes(16));

    $save = saveAlphaBox([
        "user_id"   => $userId,
        "name"      => $name,
        "email"     => $email,
        "plan"      => $plan,
        "frequency" => $freq,
        "notes"     => $notes,
        "items"     => [],
        "token"     => $token
    ]);

    // Preparar respuesta base
    $confirmationSent = false;
    $adminNoticeSent  = false;
    $id = $save['id'] ?? null;

    if ($save["success"] && $id) {
        $base = rtrim(USER_WEBSITE ?: ('https://' . ($_SERVER['HTTP_HOST'] ?? '')), '/');
        $confirmUrl = "{$base}/controllers/alphabox.php?action=confirm&id={$id}&token={$token}";

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeNotes = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');
        $planLabel = ucfirst($plan);
        $freqLabel = match ($freq) {
            'cada_2_meses' => 'Cada 2 meses',
            'cada_3_meses' => 'Cada 3 meses',
            default        => 'Mensual'
        };

        $tmpl = function($title, $content) {
            return "
            <div style='background:#f6f6f6;padding:18px;'>
              <div style='max-width:640px;margin:0 auto;background:#0f0f0f;color:#f1f1f1;border-radius:18px;overflow:hidden;border:1px solid #2a2a2a;font-family:Arial,sans-serif;'>
                <div style='background:linear-gradient(135deg,#e0b94d,#f4c842);padding:18px 20px;color:#000;font-weight:700;font-size:18px;'>
                  AlphaBox · {$title}
                </div>
                <div style='padding:20px;font-size:15px;line-height:1.6;color:#f1f1f1;'>
                  {$content}
                </div>
                <div style='padding:14px 20px;font-size:12px;color:#cfcfcf;border-top:1px solid #2a2a2a;'>
                  Nada se envía ni se cobra sin tu confirmación previa. Si no pediste esto, ignora el mensaje.
                </div>
              </div>
            </div>";
        };

        $contentUser = "
            <p style='margin:0 0 12px 0;'>Hola {$safeName},</p>
            <p style='margin:0 0 12px 0;'>Recibimos tu solicitud de AlphaBox. Antes de enviar o cobrar nada, confirma:</p>
            <div style='background:#181818;border:1px solid #2c2c2c;padding:14px;border-radius:12px;margin:12px 0;'>
              <p style='margin:4px 0;'><strong>Plan:</strong> {$planLabel}</p>
              <p style='margin:4px 0;'><strong>Frecuencia:</strong> {$freqLabel}</p>
              " . ($notes ? "<p style='margin:4px 0;'><strong>Notas:</strong> {$safeNotes}</p>" : "") . "
            </div>
            <p style='margin:12px 0; text-align:center;'>
              <a href='{$confirmUrl}' style='background:#e0b94d;color:#000;padding:14px 18px;border-radius:12px;text-decoration:none;font-weight:700;display:inline-block;'>✅ Confirmar AlphaBox</a>
            </p>
            <p style='margin:0;'>Si quieres ajustar algo, respóndenos a este email antes de confirmar.</p>
        ";
        $htmlUser = $tmpl('Confirma tu plan', $contentUser);

        $confirmationSent = sendMail($email, "Confirma tu AlphaBox", $htmlUser);

        $adminEmail = DEFAULT_CONTACT_EMAIL ?: EMISOR_EMAIL;
        $contentAdmin = "
            <p style='margin:0 0 10px 0;'>Nueva solicitud de AlphaBox:</p>
            <div style='background:#181818;border:1px solid #2c2c2c;padding:14px;border-radius:12px;margin:12px 0;'>
              <p style='margin:4px 0;'><strong>Nombre:</strong> {$safeName}</p>
              <p style='margin:4px 0;'><strong>Email:</strong> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</p>
              <p style='margin:4px 0;'><strong>Plan:</strong> {$planLabel}</p>
              <p style='margin:4px 0;'><strong>Frecuencia:</strong> {$freqLabel}</p>
              " . ($notes ? "<p style='margin:4px 0;'><strong>Notas:</strong> {$safeNotes}</p>" : "") . "
            </div>
            <p style='margin:0;'><a href='{$confirmUrl}' style='background:#e0b94d;color:#000;padding:12px 16px;border-radius:10px;text-decoration:none;font-weight:700;display:inline-block;'>Confirmar ahora</a></p>
        ";
        $htmlAdmin = $tmpl('Nueva solicitud', $contentAdmin);
        $adminNoticeSent = sendMail($adminEmail, "Nueva solicitud AlphaBox", $htmlAdmin);
    }

    $message = $save["success"]
        ? "Solicitud recibida. Te enviamos un email para confirmar antes de enviar nada."
        : ($save["message"] ?? "No pudimos guardar la solicitud.");

    echo json_encode([
        "success" => $save["success"] || $confirmationSent || $adminNoticeSent,
        "message" => $message,
        "db_saved" => $save["success"],
        "email_sent" => $confirmationSent,
        "admin_notified" => $adminNoticeSent
    ]);
}

function handleConfirmAlphaBox(): void {
    $id = intval($_GET['id'] ?? 0);
    $token = trim($_GET['token'] ?? '');

    $result = confirmAlphaBox($id, $token);

    $statusClass = $result['success'] ? 'success' : 'error';
    $msg = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');

    echo "<!doctype html><html lang='es'><head><meta charset='utf-8'><title>AlphaBox</title>
    <style>
      body { font-family: Arial, sans-serif; background:#0a0a0a; color:#fff; display:flex; align-items:center; justify-content:center; min-height:100vh; }
      .card { background:#111; padding:24px; border-radius:12px; border:1px solid #333; max-width:480px; text-align:center; }
      .success { color:#6ee7b7; }
      .error { color:#fca5a5; }
      a { color:#e0b94d; }
    </style></head><body><div class='card {$statusClass}'><h1>AlphaBox</h1><p>{$msg}</p><p><a href='/views/alphabox.php'>Volver a AlphaBox</a></p></div></body></html>";
}
