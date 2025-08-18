<?php

// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.
// ** PERBAIKAN: Menambahkan file locking (flock) untuk mencegah race condition saat token di-refresh.

session_start();
$config = include './google-oauth-config.php';

$params = isset($_GET['params']) ? $_GET['params'] : '';

// Pisahkan URL menjadi array
$parts = explode('/', $params);

// Misal kita mau otomatis bikin query array: param1, param2, param3...
$query = [];
foreach ($parts as $index => $value) {
    $query['param'.($index+1)] = $value;
}

// Contoh akses:
$fileId = $query['param1'] ?? null;
$musicId = $query['param2'] ?? null;

if (!$fileId || !$musicId) {
    http_response_code(400);
    die("fileId or musicId is required");
}


// Fungsi untuk membuat log manual
function log_message($message) {
    $logFile = 'custom.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// --- FUNGSI UNTUK MENGELOLA TOKEN DENGAN AMAN (FILE LOCKING) ---
function get_token($config) {
    $tokenFile = __DIR__ . '/token.json';
    
    // --- 1. Coba ambil dari session (cache paling cepat) ---
    if (isset($_SESSION['gdrive_token']) && time() < $_SESSION['gdrive_token']['expires_at']) {
        return $_SESSION['gdrive_token'];
    }

    // --- 2. Jika tidak ada di session, gunakan file dengan locking ---
    if (!file_exists($tokenFile)) {
        http_response_code(500);
        log_message("Token file not found.");
        die("Token file not found.");
    }

    $fp = fopen($tokenFile, 'r+');
    if (!flock($fp, LOCK_EX)) { // Kunci file secara eksklusif
        http_response_code(503);
        log_message("Could not get file lock. Server is busy.");
        die("Could not get file lock. Server is busy.");
    }

    // !! PERBAIKAN PENTING: Baca ulang file SETELAH mendapatkan lock !!
    // Ini memastikan kita mendapatkan data terbaru jika proses lain baru saja me-refresh token.
    clearstatcache(true, $tokenFile); // Hapus cache status file
    $fileSize = filesize($tokenFile);
    $tokenData = json_decode(fread($fp, $fileSize > 0 ? $fileSize : 1), true);

    // --- 3. Lakukan double-check. Refresh HANYA jika masih expired ---
    if (time() >= $tokenData['expires_at']) {
        log_message("Token expired. Attempting to refresh.");
        
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $respData = json_decode($resp, true);

        if ($httpCode !== 200 || !isset($respData['access_token'])) {
            flock($fp, LOCK_UN); // Lepas kunci sebelum mati
            fclose($fp);
            http_response_code(500);
            log_message("Failed to refresh access token: " . $resp);
            die("Failed to refresh access token: " . $resp);
        }

        $tokenData['access_token'] = $respData['access_token'];
        $tokenData['expires_at'] = time() + $respData['expires_in'] - 60; // Buffer 60 detik

        // Update file token.json
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($tokenData, JSON_PRETTY_PRINT));
        log_message("Token successfully refreshed and saved to file.");
    }

    flock($fp, LOCK_UN); // Selalu lepas kunci
    fclose($fp);

    // --- 4. Simpan di session untuk request berikutnya ---
    $_SESSION['gdrive_token'] = $tokenData;
    return $tokenData;
}

// --- Ambil Token ---
$tokenData = get_token($config);
$accessToken = $tokenData['access_token'];

// --- Ambil metadata file ---
$curlHeaders = ["Authorization: Bearer " . $accessToken];
$metaUrl = "https://www.googleapis.com/drive/v3/files/$fileId?fields=mimeType,size,name";
$chMeta = curl_init($metaUrl);
curl_setopt($chMeta, CURLOPT_HTTPHEADER, $curlHeaders);
curl_setopt($chMeta, CURLOPT_RETURNTRANSFER, true);
$metaResp = curl_exec($chMeta);
$httpCode = curl_getinfo($chMeta, CURLINFO_HTTP_CODE);
curl_close($chMeta);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    log_message("Failed to get file metadata: " . $metaResp);
    die("Failed to get file metadata: " . $metaResp);
}

$metaData = json_decode($metaResp, true);
$mimeType = $metaData['mimeType'] ?? 'application/octet-stream';
$fileSize = isset($metaData['size']) ? intval($metaData['size']) : 0;
$fileName = $metaData['name'] ?? 'file';

// --- PENANGANAN HEADER UNTUK SEEKING (BUG FIX) ---
// ** PENJELASAN: Ini adalah bagian perbaikan utama.
// ** Browser perlu tahu total ukuran file (`Content-Length`) dan bahwa server menerima `Range` request
// ** (`Accept-Ranges: bytes`) pada permintaan PERTAMA agar fitur seek bisa aktif.

header("Content-Type: $mimeType");
header("Accept-Ranges: bytes");
header("Cache-Control: public, max-age=86400"); // Cache di browser selama 1 hari
$fileNameSafe = str_replace('"', '\"', $fileName);
header("Content-Disposition: inline; filename=\"$fileNameSafe\""); // 'inline' lebih baik untuk streaming

$start = 0;
$end = $fileSize - 1;
$isPartial = false;

// Periksa apakah browser meminta bagian tertentu dari file (seeking)
if (isset($_SERVER['HTTP_RANGE'])) {
    $isPartial = true;
    preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
    $start = intval($matches[1]);
    if (!empty($matches[2])) {
        $end = intval($matches[2]);
    }
    
    // Kirim header 206 Partial Content
    header("HTTP/1.1 206 Partial Content");
    header("Content-Range: bytes $start-$end/$fileSize");
    header("Content-Length: " . ($end - $start + 1));
} else {
    // Jika ini permintaan pertama, kirim status 200 OK dan total ukuran file
    header("HTTP/1.1 200 OK");
    header("Content-Length: $fileSize");
}

// --- Stream file dengan cURL ---
$driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
$ch = curl_init($driveUrl);

// Tambahkan header 'Range' ke request cURL ke Google HANYA jika ini adalah partial request
$curlHeadersToGoogle = ["Authorization: Bearer " . $accessToken];
if ($isPartial) {
    $curlHeadersToGoogle[] = "Range: bytes=$start-$end";
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeadersToGoogle);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Nonaktifkan output buffering PHP dan kirim data langsung ke browser
// Ini penting untuk file besar agar tidak membebani memori server
if (ob_get_level() > 0) ob_end_flush();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); 
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
    echo $data;
    return strlen($data);
});

// Eksekusi cURL
curl_exec($ch);

if (curl_errno($ch)) {
    // Error tidak bisa dikirim ke browser karena header sudah terkirim,
    // jadi kita catat di log server saja.
    log_message("cURL Error on streaming: " . curl_error($ch));
    error_log("cURL Error on streaming: " . curl_error($ch));
}

curl_close($ch);
exit();