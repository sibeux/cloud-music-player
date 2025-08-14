<?php
// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.

session_start();
$config = include './google-oauth-config.php';

$fileId = $_GET['fileId'] ?? null;
if (!$fileId) {
    http_response_code(400);
    die("fileId required");
}

// --- 1. Ambil token dari session (cache memory) ---
$tokenData = $_SESSION['gdrive_token'] ?? null;

// --- 2. Load dari file kalau session kosong ---
if (!$tokenData) {
    $tokenFile = __DIR__ . '/token.json';
    if (!file_exists($tokenFile)) {
        http_response_code(500);
        die("Token file not found");
    }
    $tokenData = json_decode(file_get_contents($tokenFile), true);
}

// --- 3. Refresh token jika expired ---
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
        die("Failed to get access token");
    }

    $tokenData['access_token'] = $respData['access_token'];
    $tokenData['expires_at'] = time() + $respData['expires_in'] - 60;

    // --- Simpan di session (cache) ---
    $_SESSION['gdrive_token'] = $tokenData;

    // --- Optional: update token.json untuk persistency ---
    file_put_contents(__DIR__ . '/token.json', json_encode($tokenData, JSON_PRETTY_PRINT));
} else {
    // Simpan di session kalau belum ada
    $_SESSION['gdrive_token'] = $tokenData;
}

// --- 4. Ambil metadata file ---
$curlHeaders = ["Authorization: Bearer " . $tokenData['access_token']];
$metaUrl = "https://www.googleapis.com/drive/v3/files/$fileId?fields=mimeType,size,name";
$chMeta = curl_init($metaUrl);
curl_setopt($chMeta, CURLOPT_HTTPHEADER, $curlHeaders);
curl_setopt($chMeta, CURLOPT_RETURNTRANSFER, true);
$metaResp = curl_exec($chMeta);
curl_close($chMeta);

$metaData = json_decode($metaResp, true);
$mimeType = $metaData['mimeType'] ?? 'application/octet-stream';
$fileSize = isset($metaData['size']) ? intval($metaData['size']) : 0;
$fileName = $metaData['name'] ?? 'file';

// --- 5. Prepare Range request ---
$headers = getallheaders();
$range = $headers['Range'] ?? null;

header("Content-Type: $mimeType");
header("Accept-Ranges: bytes");
header("Cache-Control: public, max-age=86400");
header("Content-Disposition: attachment; filename=\"$fileName\"");

$start = 0;
$end = $fileSize - 1;

if ($range && preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
    $start = intval($matches[1]);
    $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
    header("Content-Range: bytes $start-$end/$fileSize");
    header("Content-Length: " . ($end - $start + 1));
    header("HTTP/1.1 206 Partial Content");
}

$curlHeaders[] = "Range: bytes=$start-$end";

// --- 6. Stream file dengan callback (tidak full memory) ---
$driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
$ch = curl_init($driveUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024);
curl_setopt($ch, CURLOPT_NOPROGRESS, false);
curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function () { }); // optional

curl_exec($ch);
if (curl_errno($ch)) {
    http_response_code(500);
    echo "Error streaming file: " . curl_error($ch);
}
curl_close($ch);