<?php
require_once '../models/order.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    die("<p style='color:red;text-align:center;font-size:18px;'>ID de pedido no proporcionado.</p>");
}

$order = getOrderById($id);

if (!$order) {
    die("<p style='color:red;text-align:center;font-size:18px;'>No se encontró el pedido con ID $id.</p>");
}

$user = $_SESSION['user'] ?? null;
$isAdmin = $user && ($user['role'] ?? null) === 'admin';
$token = $_GET['token'] ?? '';
$tokenValid = $token !== '' && !empty($order['payment_id'])
    && hash_equals((string)$order['payment_id'], (string)$token);
$userMatches = $user && !empty($user['email'])
    && strcasecmp((string)$user['email'], (string)$order['email']) === 0;

if (!$isAdmin && !$userMatches && !$tokenValid) {
    die("<p style='color:red;text-align:center;font-size:18px;'>No autorizado.</p>");
}

$numeroFactura   = $order['id'] ?? "SIN-NUMERO";
$fechaFactura = date("d/m/Y", strtotime($order['created_at']));

$emisor_nombre     = EMISOR_NAME;
$emisor_nif        = EMISOR_NIF;
$emisor_direccion  = EMISOR_ADDRESS;
$emisor_email      = EMISOR_EMAIL;
$emisor_telefono   = EMISOR_PHONE;

$clienteNombre    = $order['name'] ?? "Cliente";
$clienteEmail     = $order['email'] ?? "No especificado";
$clienteDireccion = $order['address'] ?? "No especificada";
$clienteCP        = $order['postal_code'] ?? "00000";
$clienteTelefono  = $order['phone'] ?? "No disponible";

$quantity = max(1, intval($order['quantity'] ?? 1));
$unitPrice = $quantity > 0 ? ($order['price'] / $quantity) : $order['price'];
$subtotal = $order['price'];
$iva      = $subtotal * 0.21;
$total    = $subtotal + $iva;

$itemsHTML = "
<tr>
	    <td>" . htmlspecialchars($order['product']) . "</td>
    <td style='text-align:center;'>" . $quantity . "</td>
    <td style='text-align:right;'>" . number_format($unitPrice, 2) . " €</td>
    <td style='text-align:right;'>" . number_format($subtotal, 2) . " €</td>
</tr>";

$gift = $order['gift'] ?? null;
if (!empty($gift)) {
    $giftLabelMap = [
        'prozis_bar' => 'Barritas (Prozis)'
    ];
    $giftLabel = $giftLabelMap[$gift] ?? (string)$gift;

    $itemsHTML .= "
<tr>
    <td>" . htmlspecialchars("🎁 Regalo Alpha: " . $giftLabel) . "</td>
    <td style='text-align:center;'>1</td>
    <td style='text-align:right;'>0.00 €</td>
    <td style='text-align:right;'>0.00 €</td>
</tr>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Factura #<?= htmlspecialchars($numeroFactura) ?></title>
<link rel="stylesheet" href="../css/factura.css">
</head>
<body>
<div class="a4">
  <div class="header">
    <div class="header-info">
      <p><strong>Nº Factura:</strong> <?= htmlspecialchars($numeroFactura) ?></p>
      <p><strong>Fecha:</strong> <?= htmlspecialchars($fechaFactura) ?></p>
    </div>
    <div class="logo">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
        <g fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="50" cy="50" r="46"></circle>
          <path d="M50 18 L72 75 L28 75 Z"></path>
          <path d="M47 32 C49 38, 53 42, 51 47 C49 52, 43 56, 46 61 L56 72"></path>
        </g>
      </svg>
    </div>
  </div>
  <div class="panel">
    <h2>Datos del Emisor</h2>
    <p><strong>Empresa:</strong> <?= htmlspecialchars($emisor_nombre) ?></p>
    <p><strong>NIF/CIF:</strong> <?= htmlspecialchars($emisor_nif) ?></p>
    <p><strong>Dirección:</strong> <?= htmlspecialchars($emisor_direccion) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($emisor_email) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($emisor_telefono) ?></p>
  </div>
  <div class="panel">
    <h2>Datos del Cliente</h2>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($clienteNombre) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($clienteEmail) ?></p>
    <p><strong>Dirección:</strong> <?= htmlspecialchars($clienteDireccion) ?></p>
    <p><strong>CP:</strong> <?= htmlspecialchars($clienteCP) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($clienteTelefono) ?></p>
  </div>
  <div class="panel">
    <h2>Conceptos</h2>
    <table class="factura-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th style="text-align:center;">Cantidad</th>
          <th style="text-align:right;">Precio</th>
          <th style="text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        <?= $itemsHTML ?>
      </tbody>
    </table>
  </div>
  <div class="panel total-box">
    <p><strong>Subtotal:</strong> <?= number_format($subtotal, 2) ?> €</p>
    <p><strong>IVA (21%):</strong> <?= number_format($iva, 2) ?> €</p>
    <p><strong>Total:</strong>
      <span style="color:var(--color-accent-gold); font-weight:bold;">
        <?= number_format($total, 2) ?> €
      </span>
    </p>
  </div>
  <div class="footer">
    <p>Gracias por confiar en <strong><?= htmlspecialchars($emisor_nombre) ?></strong>.</p>
    <p>Factura generada automáticamente. Documento válido sin firma.</p>
  </div>
  <div class="actions">
    <button class="btn-print" onclick="window.print()">Imprimir / Descargar PDF</button>
  </div>
</div>
</body>
</html>
