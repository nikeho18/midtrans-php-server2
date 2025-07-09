<?php
$server_key = 'SB-Mid-server-ojDn7cgXmlrJSOFIaZeji6wv';
$is_production = false;

$api_url = $is_production ?
    'https://app.midtrans.com/snap/v1/transactions' :
    'https://app.sandbox.midtrans.com/snap/v1/transactions';

if (!strpos($_SERVER['REQUEST_URI'], '/charge')) {
    http_response_code(404);
    echo json_encode(["error" => "Wrong path, must be /charge"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST allowed"]);
    exit();
}

$request_body = file_get_contents('php://input');
header('Content-Type: application/json');

$charge_result = chargeAPI($api_url, $server_key, $request_body);
http_response_code($charge_result['http_code']);

$response_body = json_decode($charge_result['body'], true);

if (isset($response_body['redirect_url'])) {
    echo json_encode([
        "redirect_url" => $response_body['redirect_url']
    ]);
} else {
    echo json_encode([
        "error" => "redirect_url not found",
        "detail" => $response_body
    ]);
}

function chargeAPI($api_url, $server_key, $request_body)
{
    $ch = curl_init();
    $curl_options = array(
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($server_key . ':')
        ),
        CURLOPT_POSTFIELDS => $request_body
    );
    curl_setopt_array($ch, $curl_options);
    $result = array(
        'body' => curl_exec($ch),
        'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    );
    return $result;
}
