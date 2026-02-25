<?php

// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.
// ** PERBAIKAN: Menambahkan file locking (flock) untuk mencegah race condition saat token di-refresh.

session_start();
require_once __DIR__ . '/../utils/utils.php';
require_once __DIR__ . '/image-dominant-color/get_color.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';
require_once __DIR__ . '/music/test-stream/get_gdrive_oauth_token.php';
include './google-oauth-config.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // Pakai safeLoad agar tidak error fatal jika file .env lupa dibuat

function streamingMusicFromGdrive($db, $musicId, $musicUrl, $fileType, $allApiData, $ffprobePath)
{
    $query = "SELECT
        m.uploader, m.is_suspicious
        FROM musics m
        WHERE m.id_music = ?;";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $musicId);
    $stmt->execute();
    $result = $stmt->get_result();
    $music = $result->fetch_assoc();
    $stmt->close();

    $params = isset($_GET['params']) ? $_GET['params'] : '';

    // Pisahkan URL menjadi array
    $parts = explode('/', $params);

    // Misal kita mau otomatis bikin query array: param1, param2, param3...
    $query = [];
    foreach ($parts as $index => $value) {
        $query['param' . ($index + 1)] = $value;
    }

    // Regex tunggal untuk menangkap ID dari kedua format URL Google Drive
    // Regex '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/' akan mencari
    // ID file baik yang diawali /d/ maupun files/
    $regexFileIdGdrive = '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/';
    preg_match($regexFileIdGdrive, $musicUrl, $matches);
    $fileIdFromUrl = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : null);

    $fileId = $fileIdFromUrl ?? null;
    $uploader = $music['uploader'] ?? null;
    $isSuspicious = $music['is_suspicious'] ?? null;

    if (!$fileId) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "error" => "fileId_not_found",
            "message" => "File ID not found",
        ]);
        die();
    }
    if (!$musicId) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "error" => "musicId_not_found",
            "message" => "Music ID not found",
        ]);
        die();
    }
    if (!$uploader) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "error" => "uploader_not_found",
            "message" => "Uploader not found",
        ]);
        die();
    }

    // Jika bukan file suspicious, pakai dari wahabinasrul
    if ($isSuspicious == 'false') {
        $uploader = "wahabinasrul@gmail.com";
    } else {
        log_message("[WARNING] File is suspicous, get refresh token from owner.");
    }

    // --- Dapatkan kredentials google oauth ---
    $config = getGoogleDriveCredentials($uploader, $allApiData);

    // --- Konfigurasi Cache Lokal ---
    // Fungsi: Menentukan lokasi dan durasi penyimpanan file cache.
    $cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host'; // Nama folder untuk menyimpan cache

    // Ambil value dari .env
    $cacheUrl = $_ENV['CACHE_FILE_URL'] ?? null;
    if (!$cacheUrl) {
        die("Error: Secret key belum disetting di .env");
    }

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

    // --- Logika Pengecekan dan Pembuatan Cache ---
    // Fungsi: Memeriksa apakah file ada di cache dan valid. Jika tidak, unduh dari GDrive.
    $isCacheValid = file_exists($cacheFilePath) && (time() - filemtime($cacheFilePath) < $cacheDuration);

    // Cek apakah file exist?
    if (!$isCacheValid) {
        log_message("[INFO] Cache MISS for fileId: $fileId. Downloading from Google Drive.");

    // --- Get Token ---
    $tokenData = getGdriveOauthToken($config, $isSuspicious);
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
    if ($isSuspicious) {
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

    // --- Lepas kunci dan tutup file handle cache ---
    // Fungsi: Menyelesaikan proses penulisan ke file cache.
    flock($cacheFp, LOCK_UN);
    fclose($cacheFp);

    } else {
        log_message("[INFO] Cache HIT for fileId: $fileId. Serving from local server.");
    }

    if ($fileType == "audio") {
        // echo json_encode($responsePayload);
        header("Location: " . $cacheFileUrl, true, 302);

        // PUTUS KONEKSI KE USER (Magic terjadi di sini) ---
        // Browser user akan mengira loading sudah selesai 100%
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request(); // Khusus PHP-FPM (Nginx/Modern Apache)
        } else {
            // Fallback jika server tidak pakai FPM (Jarang, tapi aman ditambahkan)
            ob_start();
            echo "";
            $size = ob_get_length();
            header("Content-Length: $size");
            header("Connection: close");
            ob_end_flush();
            ob_flush();
            flush();
        }

        // JALANKAN PROSES LATAR BELAKANG ---
        // Script PHP masih jalan di server, tapi user sudah tidak menunggu (loading icon di browser sudah hilang)
        if (!$isCacheValid) {
            sendToSqlCache($db, $fileId, $musicId);
    }

    // Fungsi berat ini sekarang aman dijalankan tanpa bikin user lemot
    checkCodecAudio($musicId, $cacheFilePath, $db, $ffprobePath);
    } else {
        header("Location: " . $cacheFileUrl, true, 302);
    }
}

// --- Logika untuk insert ke sql ---
function sendToSqlCache($db, $fileId, $musicId)
{
    // Masukkan ke sql bahwa file dengan ID ini telah di-cache.
    $stmt = $db->prepare("INSERT INTO cache_musics (cache_music_id) VALUES (?)");
    $stmt->bind_param("i", $musicId);
    if (!$stmt->execute()) {
        log_message("[ERROR] Error inserting recents: " . $stmt->error);
        die("Error inserting recents: " . $stmt->error);
    }
    $stmt->close();

    log_message("[SUCCESS] Caching process success for fileId: $fileId.");
}

exit();