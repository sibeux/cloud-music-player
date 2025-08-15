<?php

/**
 * Script ini bertindak sebagai gatekeeper untuk streaming file dari Google Drive
 * dengan sistem cache lokal yang efisien.
 *
 * ALUR KERJA:
 * 1. Terima permintaan dengan fileId.
 * 2. Cek apakah file dengan nama `fileId` sudah ada di direktori cache.
 * 3. JIKA TIDAK ADA (CACHE MISS):
 * a. Unduh file dari Google Drive dan simpan ke direktori cache.
 * b. Jika gagal, hentikan proses dengan error.
 * 4. JIKA ADA (CACHE HIT) atau SETELAH UNDUHAN SELESAI:
 * - Kirim header redirect 302 ke klien, mengarahkan mereka ke URL
 * file di cache. Skrip berhenti di sini.
 *
 * KEUNTUNGAN:
 * - Mengurangi penggunaan "Number of Processes" karena skrip PHP tidak pernah
 * menangani proses streaming yang lama dan berjalan sangat cepat.
 * - Proses streaming ditangani oleh server web (Apache/Nginx) yang jauh lebih
 * efisien dalam menyajikan file statis.
 */

session_start();
$config = include './google-oauth-config.php';

// Fungsi untuk membuat log manual
function log_message($message) {
    $logFile = 'custom.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// --- KONFIGURASI CACHE ---
// Path direktori di server untuk menyimpan file cache
// **PENTING**: Path ini harus sesuai dengan lokasi folder cache Anda.
$cacheDir = __DIR__ . '/../../database/mobile-music/api/cache'; 
// URL publik yang bisa diakses oleh klien untuk folder cache
// **PENTING**: URL ini harus benar dan bisa diakses dari internet.
$cacheUrl = 'https://sibeux.my.id/cloud-music-player/database/mobile-music/api/cache';

// Dapatkan fileId dari parameter GET
$fileId = $_GET['fileId'] ?? null;
if (!$fileId || !preg_match('/^[a-zA-Z0-9_-]+$/', $fileId)) { // Validasi sederhana untuk keamanan
    http_response_code(400);
    die("fileId is required and must be alphanumeric.");
}

// Pastikan direktori cache ada dan bisa ditulisi
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0755, true)) {
        http_response_code(500);
        log_message("FATAL: Failed to create cache directory at: $cacheDir");
        die("Failed to create cache directory.");
    }
}

// Tentukan path file cache dan URL publiknya
$cacheFilePath = $cacheDir . '/' . basename($fileId);
$cacheFileUrl = $cacheUrl . '/' . basename($fileId);

// --- FUNGSI UNTUK MENGELOLA TOKEN DENGAN AMAN (FILE LOCKING) ---
// (Fungsi ini tidak diubah, sudah bagus)
function get_token($config) {
    $tokenFile = __DIR__ . '/token.json';
    
    if (isset($_SESSION['gdrive_token']) && time() < $_SESSION['gdrive_token']['expires_at']) {
        return $_SESSION['gdrive_token'];
    }

    if (!file_exists($tokenFile)) {
        http_response_code(500);
        log_message("Token file not found. Please run authentication flow first.");
        die("Token file not found. Please run authentication flow first.");
    }

    $fp = fopen($tokenFile, 'r+');
    if (!flock($fp, LOCK_EX)) {
        http_response_code(503);
        log_message("Could not get file lock for token. Server is busy.");
        die("Could not get file lock for token. Server is busy.");
    }

    $tokenData = json_decode(fread($fp, filesize($tokenFile)), true);

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
            flock($fp, LOCK_UN);
            fclose($fp);
            http_response_code(500);
            log_message("Failed to refresh access token: " . $resp);
            die("Failed to refresh access token: " . $resp);
        }

        $tokenData['access_token'] = $respData['access_token'];
        $tokenData['expires_at'] = time() + $respData['expires_in'] - 60;

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($tokenData, JSON_PRETTY_PRINT));
    }

    flock($fp, LOCK_UN);
    fclose($fp);

    $_SESSION['gdrive_token'] = $tokenData;
    return $tokenData;
}


// --- LOGIKA UTAMA: CEK, UNDUH JIKA PERLU, LALU REDIRECT ---

if (!file_exists($cacheFilePath)) {
    log_message("Cache MISS for fileId: $fileId. Downloading from Google Drive.");
    
    // Ambil Token
    $tokenData = get_token($config);
    $accessToken = $tokenData['access_token'];

    // Buka file cache untuk ditulis. 'x' flag mencegah race condition.
    $cacheFp = @fopen($cacheFilePath, 'x');
    if ($cacheFp === false) {
        // File kemungkinan sedang dibuat oleh proses lain. Tunggu sebentar.
        log_message("Cache file creation race condition for $fileId. Waiting briefly.");
        usleep(500000); // Tunggu 0.5 detik
        if (!file_exists($cacheFilePath)) {
             http_response_code(500);
             log_message("Could not create cache file, even after waiting: $cacheFilePath");
             die("Could not create cache file.");
        }
    } else {
        // Kita berhasil membuat file, sekarang unduh isinya.
        $driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
        $ch = curl_init($driveUrl);
        
        $curlHeadersToGoogle = ["Authorization: Bearer " . $accessToken];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeadersToGoogle);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FILE, $cacheFp); // Arahkan output cURL langsung ke file
        
        curl_exec($ch);

        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
            log_message("cURL Error on downloading to cache: " . $curlError);
            fclose($cacheFp);
            unlink($cacheFilePath); // Hapus file yang gagal/rusak
            http_response_code(500);
            die("Failed to download file from Google Drive: " . $curlError);
        }
        
        curl_close($ch);
        fclose($cacheFp);
    }
} else {
    log_message("Cache HIT for fileId: $fileId. Redirecting to cache.");
}

// --- SELALU REDIRECT PADA AKHIRNYA ---
// Baik cache sudah ada sebelumnya atau baru saja dibuat,
// klien akan diarahkan ke file statis di cache.
header("Location: " . $cacheFileUrl, true, 302);
exit();

?>
