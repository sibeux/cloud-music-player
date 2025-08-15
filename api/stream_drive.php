<?php

/**
 * Google Drive Direct Streaming Proxy
 *
 * Arsitektur:
 * 1. Skrip ini bertindak sebagai proxy aman antara klien (Flutter) dan Google Drive.
 * 2. TIDAK menggunakan cache lokal. Semua data dialirkan (stream) secara real-time.
 * 3. Meneruskan header HTTP Range dari klien ke Google Drive untuk mendukung seeking (mencari posisi lagu).
 * 4. Mengelola refresh token dengan aman menggunakan file locking (flock) untuk mencegah race conditions.
 *
 * Kelemahan Arsitektur Saat Ini:
 * - Menggunakan satu file token.json untuk semua pengguna. Ini tidak cocok untuk aplikasi multi-user sejati.
 * Untuk multi-user, setiap pengguna harus memiliki token sendiri yang disimpan di database.
 * Namun, untuk proyek pribadi atau dengan satu akun Google, ini sudah cukup aman.
 */

session_start();
$config = include './google-oauth-config.php';

// Fungsi untuk membuat log manual
function log_message($message) {
    $logFile = 'streaming.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

/**
 * Mengelola token (mengambil dari sesi, file, atau me-refresh jika perlu) dengan aman.
 * Menggunakan file locking (flock) untuk memastikan hanya satu proses yang dapat me-refresh token pada satu waktu.
 *
 * @param array $config Konfigurasi OAuth Google.
 * @return array Data token yang valid.
 */
function get_valid_token($config) {
    $tokenFile = __DIR__ . '/token.json';

    // 1. Coba ambil dari session (cache paling cepat)
    if (isset($_SESSION['gdrive_token']) && time() < $_SESSION['gdrive_token']['expires_at']) {
        return $_SESSION['gdrive_token'];
    }

    // 2. Jika tidak ada di session, baca dari file dengan locking
    if (!file_exists($tokenFile)) {
        http_response_code(500);
        log_message("Token file not found. Please run authentication flow first.");
        die("Token file not found.");
    }

    $fp = fopen($tokenFile, 'r+');
    if (!$fp || !flock($fp, LOCK_EX)) { // Kunci file secara eksklusif
        http_response_code(503);
        log_message("Could not get file lock. Server is busy.");
        die("Could not get file lock.");
    }

    $tokenData = json_decode(stream_get_contents($fp), true);

    // 3. Refresh token jika sudah kedaluwarsa
    if (time() >= $tokenData['expires_at']) {
        log_message("Token expired. Refreshing...");
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
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $respData = json_decode($resp, true);
        if ($http_code !== 200 || !isset($respData['access_token'])) {
            flock($fp, LOCK_UN);
            fclose($fp);
            http_response_code(500);
            log_message("Failed to refresh access token. Response: " . $resp);
            die("Failed to refresh access token.");
        }

        $tokenData['access_token'] = $respData['access_token'];
        $tokenData['expires_at'] = time() + $respData['expires_in'] - 60; // Buffer 60 detik

        // Update file token.json dengan token baru
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($tokenData, JSON_PRETTY_PRINT));
        log_message("Token refreshed successfully.");
    }

    flock($fp, LOCK_UN); // Lepas kunci
    fclose($fp);

    // 4. Simpan di session untuk request berikutnya
    $_SESSION['gdrive_token'] = $tokenData;
    return $tokenData;
}


// --- MAIN LOGIC ---

$fileId = $_GET['fileId'] ?? null;
if (!$fileId) {
    http_response_code(400);
    die("fileId is required");
}

// Ambil token yang valid
$tokenData = get_valid_token($config);
$accessToken = $tokenData['access_token'];

// URL endpoint Google Drive API
$driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";

// Siapkan cURL
$ch = curl_init();

// Siapkan header untuk dikirim ke Google Drive
$headersToGoogle = [
    "Authorization: Bearer " . $accessToken
];

// ** INI BAGIAN PENTING UNTUK SEEKING / STREAMING **
// Cek apakah klien (Flutter) mengirim header 'Range'
if (isset($_SERVER['HTTP_RANGE'])) {
    // Jika iya, teruskan header tersebut ke Google Drive
    $headersToGoogle[] = "Range: " . $_SERVER['HTTP_RANGE'];
    log_message("Proxying Range request: " . $_SERVER['HTTP_RANGE']);
} else {
    log_message("Serving file from start (no Range header).");
}

// Fungsi untuk menangani header yang diterima dari Google Drive
// dan meneruskannya ke klien (Flutter)
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) {
    // Teruskan hanya header yang relevan untuk streaming
    $forwardHeaders = [
        'Content-Type',
        'Content-Length',
        'Content-Range',
        'Accept-Ranges'
    ];
    $parts = explode(':', $header, 2);
    if (count($parts) === 2 && in_array(trim($parts[0]), $forwardHeaders)) {
        header(trim($header));
    }
    return strlen($header);
});

// Fungsi untuk menulis data (potongan file) yang diterima dari Google Drive
// langsung ke output PHP (yang akan dikirim ke klien)
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
    echo $data;
    // Flush output buffer untuk memastikan data dikirim segera
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
    return strlen($data);
});

curl_setopt($ch, CURLOPT_URL, $driveUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headersToGoogle);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Eksekusi cURL
curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Set status code respons PHP sesuai dengan respons dari Google Drive
// Jika ada 'Range', Google akan merespons dengan 206 (Partial Content)
// Jika tidak, akan merespons dengan 200 (OK)
http_response_code($httpCode);

if (curl_errno($ch)) {
    log_message('cURL error: ' . curl_error($ch));
}

curl_close($ch);

exit();
