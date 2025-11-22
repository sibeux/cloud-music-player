<?php

// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.
// ** PERBAIKAN: Menambahkan file locking (flock) untuk mencegah race condition saat token di-refresh.

session_start();
require_once __DIR__ . '/../utils/utils.php';
require_once __DIR__ . '/image-dominant-color/get_color.php';
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

// Jika bukan file suspicious, pakai dari wahabinasrul
if ($isSuspicious == 'false'){
    $uploader = "wahabinasrul@gmail.com";
} else{
    log_message("[WARNING] File is suspicous, get refresh token from owner.");
}

// --- Dapatkan kredentials google oauth ---
$config = getGoogleDriveCredentials($uploader, $allApiData);

// --- Konfigurasi Cache Lokal ---
// Fungsi: Menentukan lokasi dan durasi penyimpanan file cache.
$cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host'; // Nama folder untuk menyimpan cache
$cacheUrl = 'https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/music-host';
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

$cacheFileUrl = $cacheUrl . '/' . basename($fileId);

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
        log_message("[ERROR] Token file not found. Please run authentication flow first.");
        die("Token file not found. Please run authentication flow first.");
    }

    $fp = fopen($tokenFile, 'r+');
    if (!flock($fp, LOCK_EX)) { // Kunci file secara eksklusif untuk mencegah proses lain mengganggu
        http_response_code(503);
        log_message("[ERROR] Could not get file lock. Server is busy.");
        die("Could not get file lock. Server is busy.");
    }

    $tokenData = json_decode(fread($fp, filesize($tokenFile)), true);

    // --- 3. Refresh token jika sudah expired atau file ditandai suspicous ---
    if (time() >= $tokenData['expires_at'] || $isSuspicious == 'true') {
        
        // Lakukan pengecekan jika config tidak ditemukan
        if (!$config) {
            die("Konfigurasi tidak ditemukan atau tidak lengkap.");
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
            log_message("[ERROR] Failed to refresh access token: " . $resp);
            die("Failed to refresh access token: " . $resp);
        }

        $tokenData['access_token'] = $respData['access_token'];
        $tokenData['expires_at'] = time() + $respData['expires_in'] - 60; // Kurangi 60 detik sebagai buffer

        log_message("[SUCCESS] Token has refreshed");

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

    log_message("[SUCCESS] Caching process success for fileId: $fileId.");
}

// --- Logika Pengecekan dan Pembuatan Cache ---
// Fungsi: Memeriksa apakah file ada di cache dan valid. Jika tidak, unduh dari GDrive.
$isCacheValid = file_exists($cacheFilePath) && (time() - filemtime($cacheFilePath) < $cacheDuration);

// Cek apakah file exist?
if (!$isCacheValid) {
    log_message("[INFO] Cache MISS for fileId: $fileId. Downloading from Google Drive.");
    
    // --- Get Token ---
    $tokenData = get_token($config, $isSuspicious);
    $accessToken = $tokenData['access_token'];

    // --- Buka file cache untuk ditulis ---
    // Fungsi: Membuka file di server lokal untuk menampung data dari Google Drive.
    $cacheFp = fopen($cacheFilePath, 'w');
    if (!$cacheFp) {
        http_response_code(500);
        log_message("[ERROR] Could not open cache file for writing: $cacheFilePath");
        die("Could not open cache file for writing.");
    }

    // --- Kunci file cache untuk mencegah penulisan ganda ---
    // Fungsi: Mencegah proses lain menulis ke file yang sama saat sedang diunduh.
    if (!flock($cacheFp, LOCK_EX)) {
        fclose($cacheFp);
        http_response_code(503);
        log_message("[ERROR] Could not get lock on cache file. Server is busy.");
        die("Could not get lock on cache file. Server is busy.");
    }

    // --- Downlaod file from Google Drive and save to cache ---
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
        log_message("[INFO] cURL Error on downloading to cache: " . curl_error($ch));
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
    log_message("[INFO] Cache HIT for fileId: $fileId. Serving from local server.");
}

// header("Location: " . $cacheFileUrl, true, 302);

if ($fileType == "audio") {
    if (!$isCacheValid) {
        sendToSqlCache($db, $fileId, $musicId);        
    }
    // checkCodecAudio($musicId, $cacheFilePath, $db, $ffprobePath);
    // echo json response
    sendJsonResponses([
        "success" => true,
        "music_id" => $musicId,
        "stream_url" => $cacheFileUrl,
    ]);
} else{
    header("Location: " . $cacheFileUrl, true, 302);
}

exit();