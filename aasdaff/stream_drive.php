<?php
session_start();
$config = include './google-oauth-config.php';

$fileId = $_GET['fileId'] ?? null;
if (!$fileId) {
    http_response_code(400);
    die("fileId required");
}

// --- 1. Load access token sementara ---
$tokenFile = __DIR__ . '/token.json';
$tokenData = json_decode(file_get_contents($tokenFile), true);

// --- 2. Refresh token jika expired ---
if (time() >= $tokenData['expires_at']) {
    $postData = http_build_query([
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'refresh_token' => $config['refresh_token'],
        'grant_type' => 'refresh_token',
    ]);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $resp = curl_exec($ch);
    curl_close($ch);

    $respData = json_decode($resp, true);
    if (!isset($respData['access_token'])) {
        http_response_code(500);
        die("Failed to get access token. Response: " . print_r($respData, true));
    }

    $tokenData['access_token'] = $respData['access_token'];
    $tokenData['expires_at'] = time() + $respData['expires_in'] - 60;
    file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));
}

// --- 3. Prepare headers untuk chunked / seek ---
$headers = getallheaders();
$range = $headers['Range'] ?? null;

$curlHeaders = [
    "Authorization: Bearer " . $tokenData['access_token']
];

$driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
$ch = curl_init($driveUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// --- 4. Ambil metadata untuk MIME type & file size ---
$metaUrl = "https://www.googleapis.com/drive/v3/files/$fileId?fields=mimeType,size";
$chMeta = curl_init($metaUrl);
curl_setopt($chMeta, CURLOPT_HTTPHEADER, $curlHeaders);
curl_setopt($chMeta, CURLOPT_RETURNTRANSFER, true);
$metaResp = curl_exec($chMeta);
curl_close($chMeta);

$metaData = json_decode($metaResp, true);
$mimeType = $metaData['mimeType'] ?? 'application/octet-stream';
$fileSize = isset($metaData['size']) ? intval($metaData['size']) : 0;

// --- 5. Kirim header ke client ---
header("Content-Type: $mimeType");
header("Accept-Ranges: bytes");
header("Cache-Control: public, max-age=86400"); // cache 1 hari

if ($range) {
    if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
        $start = intval($matches[1]);
        $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
        header("Content-Range: bytes $start-$end/$fileSize");
        header("Content-Length: " . ($end - $start + 1));
        header("HTTP/1.1 206 Partial Content");

        $curlHeaders[] = "Range: bytes=$start-$end";
    }
} else {
    header("Content-Length: $fileSize");
}

curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024); // 1MB per chunk
curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo "Error streaming file: " . curl_error($ch);
}
curl_close($ch);