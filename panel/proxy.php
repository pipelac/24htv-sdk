<?php

/**
 * 24TV API Proxy
 *
 * Легковесный прокси для обхода CORS-ограничений при запросах к API 24часаТВ.
 * Используется панелью управления (panel/index.html).
 *
 * @example POST /panel/proxy.php
 *   Body: {"url": "/users/12680", "method": "GET", "token": "xxx", "baseUrl": "https://provapi.24h.tv/v2", "body": null}
 */

@ini_set('always_populate_raw_post_data', '-1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Only POST allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['url']) || !isset($input['method']) || !isset($input['token'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Required: url, method, token'], JSON_UNESCAPED_UNICODE);
    exit;
}

$baseUrl = isset($input['baseUrl']) ? rtrim($input['baseUrl'], '/') : 'https://provapi.24h.tv/v2';
$apiUrl  = $baseUrl . '/' . ltrim($input['url'], '/');
$method  = strtoupper($input['method']);
$token   = $input['token'];
$body    = isset($input['body']) ? $input['body'] : null;

// Добавить token в URL
$separator = strpos($apiUrl, '?') !== false ? '&' : '?';
$apiUrl .= $separator . 'token=' . urlencode($token);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

switch ($method) {
    case 'POST':
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }
        break;
    case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }
        break;
    case 'PATCH':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }
        break;
    case 'DELETE':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }
        break;
    case 'GET':
    default:
        break;
}

$startTime = microtime(true);
$response  = curl_exec($ch);
$elapsed   = round((microtime(true) - $startTime) * 1000);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error     = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode([
        'ok'        => false,
        'error'     => 'cURL error: ' . $error,
        'http_code' => 0,
        'time_ms'   => $elapsed,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Попытка разобрать JSON-ответ
$decoded = json_decode($response, true);
if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    $decoded = $response; // вернуть raw если не JSON
}

echo json_encode([
    'ok'        => $httpCode >= 200 && $httpCode < 300,
    'data'      => $decoded,
    'http_code' => $httpCode,
    'time_ms'   => $elapsed,
    'api_url'   => preg_replace('/token=[^&]+/', 'token=***', $apiUrl),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
