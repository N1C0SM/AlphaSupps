<?php
require_once './config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$total = isset($input['total']) ? floatval($input['total']) : 0;

$ch = curl_init("https://api.stripe.com/v1/payment_intents");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ":");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
  "amount" => intval(round($total * 100)),
  "currency" => "eur",
  "automatic_payment_methods[enabled]" => "true"
]));
$response = curl_exec($ch);

echo $response;
