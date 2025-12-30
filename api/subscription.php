<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/order.php'; // sendMail + config/DB

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para continuar.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!$input) {
    parse_str($raw, $input);
}

$action = $input['action'] ?? '';
$allowed = ['pause', 'cancel', 'change_interval'];
if (!in_array($action, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    exit;
}

$subscription = $input['subscription'] ?? [];
if (!is_array($subscription) || empty($subscription['name'])) {
    echo json_encode(['success' => false, 'message' => 'Suscripción inválida.']);
    exit;
}

$note = trim((string)($input['note'] ?? ''));
$newInterval = intval($input['new_interval'] ?? 0);
if ($action === 'change_interval' && ($newInterval < 1 || $newInterval > 3)) {
    echo json_encode(['success' => false, 'message' => 'Frecuencia inválida.']);
    exit;
}

$payload = [
    'user_id' => $_SESSION['user']['id'],
    'email' => $_SESSION['user']['email'] ?? null,
    'name' => $_SESSION['user']['name'] ?? 'Cliente',
    'action' => $action,
    'subscription' => [
        'name' => $subscription['name'] ?? null,
        'interval' => $subscription['interval'] ?? null,
        'qty' => $subscription['qty'] ?? null,
        'order_id' => $subscription['order_id'] ?? null,
        'last_order_at' => $subscription['last_order_at'] ?? null
    ],
    'new_interval' => $action === 'change_interval' ? $newInterval : null,
    'note' => $note !== '' ? $note : null,
    'created_at' => date('c')
];

// ----------------------------------
// Registrar en log interno
// ----------------------------------
$logDir = __DIR__ . '/../cache';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/subscription_requests.log';
file_put_contents($logFile, json_encode($payload) . PHP_EOL, FILE_APPEND);

// ----------------------------------
// Helpers Stripe
// ----------------------------------
function stripeRequest(string $url, array $payload, string $secretKey, string $method = 'POST'): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ":");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    if (strtoupper($method) === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ["error" => $error ?: "Error desconocido al conectar con Stripe"];
    }
    curl_close($ch);

    $decoded = json_decode($response, true);
    if ($httpCode >= 400 || isset($decoded["error"])) {
        $msg = $decoded["error"]["message"] ?? "Stripe devolvió un error ({$httpCode})";
        return ["error" => $msg, "stripe_response" => $decoded];
    }

    return $decoded ?: ["error" => "Respuesta vacía de Stripe"];
}

function findSubscriptionIdByOrder($orderId): ?string {
    $mapFile = __DIR__ . '/../cache/stripe_subscriptions.log';
    if (!file_exists($mapFile) || !is_readable($mapFile)) return null;
    $lines = file($mapFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $subscriptionId = null;
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (!is_array($entry)) continue;
        if ((string)($entry['order_id'] ?? '') === (string)$orderId) {
            $subscriptionId = $entry['subscription_id'] ?? null;
        }
    }
    return $subscriptionId;
}

// ----------------------------------
// Enviar email de aviso
// ----------------------------------
$actionLabels = [
    'pause' => 'Pausa de suscripción',
    'cancel' => 'Cancelación de suscripción',
    'change_interval' => 'Cambio de frecuencia'
];

$supportEmail = defined('DEFAULT_CONTACT_EMAIL') ? DEFAULT_CONTACT_EMAIL : 'contact@alphasupps.com';
$userEmail = $payload['email'] ?? null;

$subscriptionName = htmlspecialchars((string)($payload['subscription']['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$currentInterval = (int)($payload['subscription']['interval'] ?? 0);
$qty = (int)($payload['subscription']['qty'] ?? 1);
$orderId = htmlspecialchars((string)($payload['subscription']['order_id'] ?? ''), ENT_QUOTES, 'UTF-8');
$lastOrder = htmlspecialchars((string)($payload['subscription']['last_order_at'] ?? ''), ENT_QUOTES, 'UTF-8');
$noteHtml = $payload['note']
    ? '<p style="margin:4px 0 0;font-size:14px;color:#f5f5f5;"><strong>Nota:</strong> ' . htmlspecialchars($payload['note'], ENT_QUOTES, 'UTF-8') . '</p>'
    : '';
$newIntervalHtml = ($action === 'change_interval' && $payload['new_interval'])
    ? '<p style="margin:4px 0 0;font-size:14px;color:#f5f5f5;"><strong>Nueva frecuencia:</strong> cada ' . intval($payload['new_interval']) . ' meses</p>'
    : '';

$actionLabel = $actionLabels[$action] ?? $action;
$subject = ($actionLabels[$action] ?? 'Gestión de suscripción') . " · {$subscriptionName}";
$ctaUrl = 'https://alphasupps.alwaysdata.net/views/subscriptions.php';

$emailBody = "
<div style='background:#0d0d0f;padding:24px 20px;color:#eee;font-family:Inter,Arial,sans-serif;border-radius:14px;max-width:640px;margin:auto;border:1px solid rgba(255,255,255,0.12);box-shadow:0 18px 38px rgba(0,0,0,0.35);'>
  <p style='margin:0 0 6px;font-size:13px;letter-spacing:0.08em;color:#f5d27a;font-weight:700;text-transform:uppercase;'>Solicitud de suscripción</p>
  <h2 style='margin:0 0 14px;font-size:22px;color:#fff;'>{$subject}</h2>
  <p style='margin:0 0 12px;font-size:15px;line-height:1.6;color:#dcdcdc;'>
    Acción solicitada: <strong>{$actionLabel}</strong>
  </p>
  <div style='background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);padding:14px 16px;border-radius:12px;'>
    <p style='margin:0;font-size:15px;color:#f5f5f5;'><strong>Producto:</strong> {$subscriptionName}</p>
    <p style='margin:4px 0 0;font-size:14px;color:#f5f5f5;'><strong>Frecuencia actual:</strong> cada {$currentInterval} meses</p>
    {$newIntervalHtml}
    <p style='margin:4px 0 0;font-size:14px;color:#f5f5f5;'><strong>Cantidad:</strong> {$qty}</p>
    <p style='margin:4px 0 0;font-size:14px;color:#f5f5f5;'><strong>Último pedido:</strong> #{$orderId} · {$lastOrder}</p>
    <p style='margin:4px 0 0;font-size:14px;color:#f5f5f5;'><strong>Cliente:</strong> {$payload['name']} ({$userEmail})</p>
    {$noteHtml}
  </div>
  <div style='text-align:center;margin-top:22px;'>
    <a href='{$ctaUrl}' style='display:inline-block;padding:12px 18px;border-radius:10px;background:#f5d27a;color:#111;font-weight:800;text-decoration:none;box-shadow:0 8px 18px rgba(0,0,0,0.3);'>Abrir panel de suscripciones</a>
  </div>
  <p style='margin:18px 0 0;font-size:13px;color:#9ea3b0;'>Generado automáticamente - AlphaSupps</p>
</div>";

$userBody = "
<div style='background:#0d0d0f;padding:24px 20px;color:#eee;font-family:Inter,Arial,sans-serif;border-radius:14px;max-width:640px;margin:auto;border:1px solid rgba(255,255,255,0.12);box-shadow:0 18px 38px rgba(0,0,0,0.35);'>
  <h2 style='margin:0 0 10px;font-size:22px;color:#f5d27a;'>Hemos recibido tu solicitud</h2>
  <p style='margin:0 0 12px;font-size:15px;line-height:1.6;color:#dcdcdc;'>Acción: <strong>{$actionLabel}</strong></p>
  <p style='margin:0 0 10px;font-size:14px;line-height:1.6;color:#dcdcdc;'>Producto: <strong>{$subscriptionName}</strong> · Frecuencia: cada {$currentInterval} meses · Cantidad: {$qty}</p>
  {$newIntervalHtml}
  {$noteHtml}
  <p style='margin:14px 0 0;font-size:14px;line-height:1.6;color:#dcdcdc;'>Te confirmaremos el cambio en menos de 48h. Si necesitas algo urgente, responde a este email.</p>
  <div style='text-align:center;margin-top:18px;'>
    <a href='{$ctaUrl}' style='display:inline-block;padding:12px 18px;border-radius:10px;background:#f5d27a;color:#111;font-weight:800;text-decoration:none;box-shadow:0 8px 18px rgba(0,0,0,0.3);'>Ver mi suscripción</a>
  </div>
</div>";

$emailSent = sendMail($supportEmail, $subject, $emailBody);
if ($userEmail) {
    sendMail($userEmail, 'Tu solicitud de suscripción en AlphaSupps', $userBody);
}

$stripeCanceled = false;
if ($action === 'cancel') {
    $subId = findSubscriptionIdByOrder($payload['subscription']['order_id'] ?? null);
    if ($subId) {
        $cancelRes = stripeRequest("https://api.stripe.com/v1/subscriptions/{$subId}", [], STRIPE_SECRET_KEY, 'DELETE');
        $stripeCanceled = empty($cancelRes['error']);
    }
}

$message = $emailSent
    ? 'Solicitud recibida y enviada a soporte. Te confirmaremos por email en menos de 48h.'
    : 'Solicitud registrada. No pudimos enviar el email automáticamente, revisaremos tu caso igualmente.';

if ($stripeCanceled) {
    $message = 'Suscripción cancelada en Stripe. Te llegará confirmación por email.';
} elseif ($action === 'cancel' && !$stripeCanceled) {
    $message = 'No pudimos cancelar automáticamente en Stripe. Revisaremos tu solicitud en menos de 48h.';
}

echo json_encode([
    'success' => true,
    'message' => $message
]);
