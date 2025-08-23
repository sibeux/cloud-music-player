<?php

// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.
// ** PERBAIKAN: Menambahkan file locking (flock) untuk mencegah race condition saat token di-refresh.

global $ffprobePath, $db;
session_start();
require_once __DIR__ . '/../database/mobile-music-player/api/connection.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';
include './google-oauth-config.php';

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
$uploader = $query['param3'] ?? null;
$isSuspicious = $query['param4'] ?? null;
$fileType = $query['param5'] ?? null;

if (!$fileId) {
    http_response_code(400);
    die("fileId is required");
} 
if (!$musicId) {
    http_response_code(400);
    die("musicId is required");
} 
if (!$uploader) {
    http_response_code(400);
    die("uploader is required");
}
if (!$isSuspicious) {
    http_response_code(400);
    die("isSuspicious is required");
}

// Ambil credentials sesuai email
if ($uploader == 'cybeat'){
    $uploader = "sibesibe86@gmail.com";
}

// --- Dapatkan kredentials google oauth ---
$config = getGoogleDriveCredentials($uploader, $allApiData);

// Fungsi untuk membuat log manual
function log_message($message) {
    $logFile = 'custom.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// --- Konfigurasi Cache Lokal ---
// Fungsi: Menentukan lokasi dan durasi penyimpanan file cache.
$cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host'; // Nama folder untuk menyimpan cache
// Fungsi $cacheDuration adalah untuk mendownload ulang file dari GDRIVE-
// jika sudah expired. Kita set ke 1 tahun, karena file lagu statis banget.
$cacheDuration = 31536000; // Durasi cache dalam detik (86400 = 24 jam)

// --- Pastikan direktori cache ada dan bisa ditulisi ---
// Fungsi: Membuat folder cache jika belum ada.
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0755, true)) {
        http_response_code(500);
        die("Failed to create cache directory.");
    }
}

// --- Tentukan path file cache ---
// Fungsi: Membuat path file unik untuk setiap fileId di dalam folder cache.
// basename() digunakan untuk keamanan, mencegah directory traversal.
$cacheFilePath = $cacheDir . '/' . basename($fileId);

// --- FUNGSI UNTUK MENGELOLA TOKEN DENGAN AMAN (FILE LOCKING) ---
function get_token($config, $isSuspicious) {
    $tokenFile = __DIR__ . '/token.json';
    
    // --- 1. Coba ambil dari session (cache paling cepat) ---
    if (isset($_SESSION['gdrive_token']) && time() < $_SESSION['gdrive_token']['expires_at']) {
        return $_SESSION['gdrive_token'];
    }

    // --- 2. Jika tidak ada di session atau sudah expired, baca dari file ---
    if (!file_exists($tokenFile)) {
        http_response_code(500);
        log_message("Token file not found. Please run authentication flow first.");
        die("Token file not found. Please run authentication flow first.");
    }

    $fp = fopen($tokenFile, 'r+');
    if (!flock($fp, LOCK_EX)) { // Kunci file secara eksklusif untuk mencegah proses lain mengganggu
        http_response_code(503);
        log_message("Could not get file lock. Server is busy.");
        die("Could not get file lock. Server is busy.");
    }

    $tokenData = json_decode(fread($fp, filesize($tokenFile)), true);

    // --- 3. Refresh token jika sudah expired atau file ditandai suspicous ---
    if (time() >= $tokenData['expires_at'] || $isSuspicious == true) {
        
        // Lakukan pengecekan jika config tidak ditemukan
        if (!$config) {
            die("Konfigurasi untuk email '{$email}' tidak ditemukan atau tidak lengkap.");
        }

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
            flock($fp, LOCK_UN); // Lepas kunci sebelum mati
            fclose($fp);
            http_response_code(500);
            log_message($config['client_id'] . $config['client_secret'] . $config['refresh_token']);
            log_message("Failed to refresh access token: " . $resp);
            die("Failed to refresh access token: " . $resp);
        }

        $tokenData['access_token'] = $respData['access_token'];
        $tokenData['expires_at'] = time() + $respData['expires_in'] - 60; // Kurangi 60 detik sebagai buffer

        // Update file token.json dengan token baru
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($tokenData, JSON_PRETTY_PRINT));
    }

    flock($fp, LOCK_UN); // Lepas kunci
    fclose($fp);

    // --- 4. Simpan di session untuk request berikutnya ---
    $_SESSION['gdrive_token'] = $tokenData;
    return $tokenData;
}

// --- Logika untuk insert ke sql ---
function sendToSqlCache($db, $fileId, $musicId){
    // Masukkan ke sql bahwa file dengan ID ini telah di-cache.
    $stmt = $db->prepare("INSERT INTO cache_music (cache_music_id) VALUES (?)");
    $stmt->bind_param("i", $musicId);
    if (!$stmt->execute()) {
        die("Error inserting recents: " . $stmt->error);
    }
    $stmt->close();

    log_message("Caching process success for fileId: $fileId.");
}

// --- Logika Pengecekan dan Pembuatan Cache ---
// Fungsi: Memeriksa apakah file ada di cache dan valid. Jika tidak, unduh dari GDrive.
$isCacheValid = file_exists($cacheFilePath) && (time() - filemtime($cacheFilePath) < $cacheDuration);

// Cek apakah file exist?
if (!$isCacheValid) {
    log_message("Cache MISS for fileId: $fileId. Downloading from Google Drive.");
    
    // --- Get Token ---
    $tokenData = get_token($config, $isSuspicious);
    $accessToken = $tokenData['access_token'];

    // --- Buka file cache untuk ditulis ---
    // Fungsi: Membuka file di server lokal untuk menampung data dari Google Drive.
    $cacheFp = fopen($cacheFilePath, 'w');
    if (!$cacheFp) {
        http_response_code(500);
        log_message("Could not open cache file for writing: $cacheFilePath");
        die("Could not open cache file for writing.");
    }

    // --- Kunci file cache untuk mencegah penulisan ganda ---
    // Fungsi: Mencegah proses lain menulis ke file yang sama saat sedang diunduh.
    if (!flock($cacheFp, LOCK_EX)) {
        fclose($cacheFp);
        http_response_code(503);
        log_message("Could not get lock on cache file. Server is busy.");
        die("Could not get lock on cache file. Server is busy.");
    }

    // --- Unduh file dari Google Drive dan simpan ke cache ---
    $driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
    if ($isSuspicious){
        // acknowledgeAbuse=true berlaku untuk file suspicious
        $driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media&acknowledgeAbuse=true";
    }
    $ch = curl_init($driveUrl);
    
    $curlHeadersToGoogle = ["Authorization: Bearer " . $accessToken];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeadersToGoogle);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // --- Alihkan output cURL ke file cache, bukan ke browser ---
    // Fungsi: Opsi ini mengarahkan semua data yang diterima cURL untuk ditulis ke file handle ($cacheFp).
    curl_setopt($ch, CURLOPT_FILE, $cacheFp);
    
    curl_exec($ch);

    if (curl_errno($ch)) {
        log_message("cURL Error on downloading to cache: " . curl_error($ch));
        // --- Hapus file cache yang gagal/rusak ---
        // Fungsi: Membersihkan file yang tidak lengkap jika unduhan gagal.
        flock($cacheFp, LOCK_UN);
        fclose($cacheFp);
        unlink($cacheFilePath); // Hapus file yang gagal
        http_response_code(500);
        die("Failed to download file from Google Drive.");
    }
    
    curl_close($ch);

    // --- Lepas kunci dan tutup file handle cache ---
    // Fungsi: Menyelesaikan proses penulisan ke file cache.
    flock($cacheFp, LOCK_UN);
    fclose($cacheFp);

} else {
    log_message("Cache HIT for fileId: $fileId. Serving from local server.");
}


// --- BAGIAN PENYAJIAN FILE (STREAMING DARI CACHE LOCAL) ---
// Fungsi: Bagian ini sekarang selalu menyajikan file dari cache local, baik yang baru diunduh maupun yang sudah ada.

// --- Ambil metadata dari file LOKAL ---
$fileSize = filesize($cacheFilePath);
$mimeType = mime_content_type($cacheFilePath) ?: 'application/octet-stream';

// --- get nama file asli dari Google Drive (optional, tapi good for 'Content-Disposition') ---
// Kita hanya need melakukan ini sekali if cache baru dibuat, tapi untuk simplicitas kita query lagi.
// Untuk performa lebih, nama file can saved di file terpisah ex: `cache/fileId.meta`.
$tokenData = get_token($config, $isSuspicious);
$accessToken = $tokenData['access_token'];
$metaUrl = "https://www.googleapis.com/drive/v3/files/$fileId?fields=name";
$chMeta = curl_init($metaUrl);
curl_setopt($chMeta, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $accessToken]);
curl_setopt($chMeta, CURLOPT_RETURNTRANSFER, true);
$metaResp = curl_exec($chMeta);
curl_close($chMeta);
$metaData = json_decode($metaResp, true);
$fileName = $metaData['name'] ?? $fileId; // Use fileId for fallback

// --- PENANGANAN HEADER FOR SEEKING (BUG FIX) ---
header("Content-Type: $mimeType");
header("Accept-Ranges: bytes");
header("Cache-Control: public, max-age=86400");
$fileNameSafe = str_replace('"', '\"', $fileName);
header("Content-Disposition: inline; filename=\"$fileNameSafe\"");

$start = 0;
$end = $fileSize - 1;

if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
    $start = intval($matches[1]);
    if (!empty($matches[2])) {
        $end = intval($matches[2]);
    }
    
    header("HTTP/1.1 206 Partial Content");
    header("Content-Range: bytes $start-$end/$fileSize");
    header("Content-Length: " . ($end - $start + 1));
} else {
    header("HTTP/1.1 200 OK");
    header("Content-Length: $fileSize");
}

// --- Stream file dari CACHE LOCAL with PHP ---
// Function: Read file dari disk server dan send it ke browser via "potongan" (chunk) for memory efficiency.
$localFp = fopen($cacheFilePath, 'rb');
fseek($localFp, $start);
$bytesSent = 0;
$chunkSize = 8192; // 8KB per chunk

// Deactivate output buffering PHP
if (ob_get_level() > 0) ob_end_flush();

while (!feof($localFp) && ($bytesSent < ($end - $start + 1)) && !connection_aborted()) {
    $bytesToRead = min($chunkSize, ($end - $start + 1) - $bytesSent);
    echo fread($localFp, $bytesToRead);
    $bytesSent += $bytesToRead;
    flush(); // Send output ke browser soon
}

fclose($localFp);
if ($fileType == "audio") {
    sendToSqlCache($db, $fileId, $musicId);
    checkCodecAudio($musicId, $cacheFilePath, $db, $ffprobePath);
}
exit();