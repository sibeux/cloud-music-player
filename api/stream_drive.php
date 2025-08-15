<?php

// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.
// ** PERBAIKAN: Menambahkan file locking (flock) untuk mencegah race condition saat token di-refresh.

session_start();
$config = include './google-oauth-config.php';

// Fungsi untuk membuat log manual
function log_message($message) {
    $logFile = 'custom.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// --- BARU: Konfigurasi Cache Lokal ---
// Fungsi: Menentukan lokasi dan durasi penyimpanan file cache.
$cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host'; // Nama folder untuk menyimpan cache
// URL publik yang bisa diakses oleh klien untuk folder cache
// **PENTING**: URL ini harus benar dan bisa diakses dari internet.
$cacheUrl = 'https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/music-host';
// Fungsi $cacheDuration adalah untuk mendownload ulang file dari GDRIVE-
// jika sudah expired. Kita set ke 1 tahun, karena file lagu statis banget.
$cacheDuration = 31536000; // Durasi cache dalam detik (86400 = 24 jam)

// Dapatkan file id GDRIVE lewat method GET.
$fileId = $_GET['fileId'] ?? null;
if (!$fileId) {
    http_response_code(400);
    die("fileId is required");
}

$cacheFileUrl = $cacheUrl . '/' . basename($fileId);

// --- BARU: Pastikan direktori cache ada dan bisa ditulisi ---
// Fungsi: Membuat folder cache jika belum ada.
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0755, true)) {
        http_response_code(500);
        die("Failed to create cache directory.");
    }
}

// --- BARU: Tentukan path file cache ---
// Fungsi: Membuat path file unik untuk setiap fileId di dalam folder cache.
// basename() digunakan untuk keamanan, mencegah directory traversal.
$cacheFilePath = $cacheDir . '/' . basename($fileId);

// --- FUNGSI UNTUK MENGELOLA TOKEN DENGAN AMAN (FILE LOCKING) ---
function get_token($config) {
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

    // --- 3. Refresh token jika sudah expired ---
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
            flock($fp, LOCK_UN); // Lepas kunci sebelum mati
            fclose($fp);
            http_response_code(500);
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

// --- BARU: Logika Pengecekan dan Pembuatan Cache ---
// Fungsi: Memeriksa apakah file ada di cache dan valid. Jika tidak, unduh dari GDrive.
$isCacheValid = file_exists($cacheFilePath) && (time() - filemtime($cacheFilePath) < $cacheDuration);

if (!$isCacheValid) {
    log_message("Cache MISS for fileId: $fileId. Downloading from Google Drive.");
    
    // --- Ambil Token ---
    $tokenData = get_token($config);
    $accessToken = $tokenData['access_token'];

    // --- Buka file cache untuk ditulis ---
    // Fungsi: Membuka file di server lokal untuk menampung data dari Google Drive.
    $cacheFp = fopen($cacheFilePath, 'w');
    if (!$cacheFp) {
        http_response_code(500);
        log_message("Could not open cache file for writing: $cacheFilePath");
        die("Could not open cache file for writing.");
    }

    // --- BARU: Kunci file cache untuk mencegah penulisan ganda ---
    // Fungsi: Mencegah proses lain menulis ke file yang sama saat sedang diunduh.
    if (!flock($cacheFp, LOCK_EX)) {
        fclose($cacheFp);
        http_response_code(503);
        log_message("Could not get lock on cache file. Server is busy.");
        die("Could not get lock on cache file. Server is busy.");
    }

    // --- Unduh file dari Google Drive dan simpan ke cache ---
    $driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
    $ch = curl_init($driveUrl);
    
    $curlHeadersToGoogle = ["Authorization: Bearer " . $accessToken];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeadersToGoogle);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // --- BARU: Alihkan output cURL ke file cache, bukan ke browser ---
    // Fungsi: Opsi ini mengarahkan semua data yang diterima cURL untuk ditulis ke file handle ($cacheFp).
    curl_setopt($ch, CURLOPT_FILE, $cacheFp);
    
    curl_exec($ch);

    if (curl_errno($ch)) {
        log_message("cURL Error on downloading to cache: " . curl_error($ch));
        // --- BARU: Hapus file cache yang gagal/rusak ---
        // Fungsi: Membersihkan file yang tidak lengkap jika unduhan gagal.
        flock($cacheFp, LOCK_UN);
        fclose($cacheFp);
        unlink($cacheFilePath); // Hapus file yang gagal
        http_response_code(500);
        die("Failed to download file from Google Drive.");
    }
    
    curl_close($ch);

    // --- BARU: Lepas kunci dan tutup file handle cache ---
    // Fungsi: Menyelesaikan proses penulisan ke file cache.
    flock($cacheFp, LOCK_UN);
    fclose($cacheFp);

} else {
    log_message("Cache HIT for fileId: $fileId. Redirect to local server.");
}


// --- SELALU REDIRECT PADA AKHIRNYA ---
// Baik cache sudah ada sebelumnya atau baru saja dibuat,
// klien akan diarahkan ke file statis di cache.
header("Location: " . $cacheFileUrl, true, 302);
exit();