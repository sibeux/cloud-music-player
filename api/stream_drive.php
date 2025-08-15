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

// --- Konfigurasi Cache Lokal ---
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

// --- Logika Pengecekan dan Pembuatan Cache ---
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
    log_message("Cache HIT for fileId: $fileId.");
}

// Fungsi: Memeriksa tipe MIME file di cache untuk menentukan tindakan selanjutnya.
// Ini penting agar request untuk pre-cache gambar tidak di-redirect.

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $cacheFilePath);
finfo_close($finfo);

    // Jika tipe file adalah gambar, hentikan eksekusi dan jangan redirect.
    // Klien akan mendapatkan respons kosong (200 OK), yang menandakan pre-cache berhasil.
if (strpos($mimeType, 'image/') === 0) {
    log_message("File is an image ($mimeType). Pre-cache successful. No redirect needed.");

        // --- BAGIAN PENYAJIAN FILE (STREAMING DARI CACHE LOKAL) ---
        // Fungsi: Bagian ini sekarang selalu menyajikan file dari cache lokal, baik yang baru diunduh maupun yang sudah ada.

        // --- Ambil metadata dari file LOKAL ---
    $fileSize = filesize($cacheFilePath);
    $mimeType = mime_content_type($cacheFilePath) ?: 'application/octet-stream';

        // --- Ambil nama file asli dari Google Drive (opsional, tapi bagus untuk 'Content-Disposition') ---
        // Kita hanya perlu melakukan ini sekali jika cache baru dibuat, tapi untuk simplicitas kita query lagi.
        // Untuk performa lebih, nama file bisa disimpan di file terpisah misal `cache/fileId.meta`.
    $tokenData = get_token($config);
    $accessToken = $tokenData['access_token'];
    $metaUrl = "https://www.googleapis.com/drive/v3/files/$fileId?fields=name";
    $chMeta = curl_init($metaUrl);
    curl_setopt($chMeta, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $accessToken]);
    curl_setopt($chMeta, CURLOPT_RETURNTRANSFER, true);
    $metaResp = curl_exec($chMeta);
    curl_close($chMeta);
    $metaData = json_decode($metaResp, true);
        $fileName = $metaData['name'] ?? $fileId; // Gunakan fileId sebagai fallback

        // --- PENANGANAN HEADER UNTUK SEEKING (BUG FIX) ---
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

        // --- BARU: Stream file dari CACHE LOKAL dengan PHP ---
        // Fungsi: Membaca file dari disk server dan mengirimkannya ke browser dalam potongan (chunk) untuk efisiensi memori.
        $localFp = fopen($cacheFilePath, 'rb');
        fseek($localFp, $start);
        $bytesSent = 0;
        $chunkSize = 8192; // 8KB per chunk

        // Nonaktifkan output buffering PHP
        if (ob_get_level() > 0) ob_end_flush();

        while (!feof($localFp) && ($bytesSent < ($end - $start + 1)) && !connection_aborted()) {
            $bytesToRead = min($chunkSize, ($end - $start + 1) - $bytesSent);
            echo fread($localFp, $bytesToRead);
            $bytesSent += $bytesToRead;
            flush(); // Kirim output ke browser segera
        }

        fclose($localFp);

        // Hentikan script di sini agar tidak terjadi redirect.
        exit();
    }
    // Jika tipe file adalah audio atau lainnya, biarkan script melanjutkan ke redirect.
    elseif (strpos($mimeType, 'audio/') === 0) {
        log_message("File is audio ($mimeType). Proceeding to redirect to: $cacheFileUrl");
    }
    else {
       log_message("File is other type ($mimeType). Proceeding to redirect to: $cacheFileUrl");
   }



// --- SELALU REDIRECT PADA AKHIRNYA ---
// Baik cache sudah ada sebelumnya atau baru saja dibuat,
// klien akan diarahkan ke file statis di cache.
   header("Location: " . $cacheFileUrl, true, 302);
   exit();
