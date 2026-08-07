<?php

// ** Kelemahan kode ini yaitu dia menghabiskan jatah "Number of Processes" dari cpanel.
// ** Kalau yang pakai aplikasi banyak, bisa jadi error.
// ** PERBAIKAN: Menambahkan file locking (flock) untuk mencegah race condition saat token di-refresh.

require_once __DIR__ . '/../utils/utils.php';
require_once __DIR__ . '/image-dominant-color/get_color.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';
require_once __DIR__ . '/music/stream/get_gdrive_oauth_token.php';
require_once __DIR__ . '/google-oauth-config.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // Pakai safeLoad agar tidak error fatal jika file .env lupa dibuat

function streamingMusicFromGdrive($db, $musicId, $mediaUrl, $fileType, $allApiData, $ffprobePath)
{
    $query = "SELECT
        m.uploader, m.is_suspicious
        FROM musics m
        WHERE m.id_music = ?;";

    $music = [];

    if ($fileType !== "image") {
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $musicId);
        $stmt->execute();
        $result = $stmt->get_result();
        $music = $result->fetch_assoc();
        $stmt->close();
    }

    // Regex tunggal untuk menangkap ID dari kedua format URL Google Drive
    // Regex '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/' akan mencari
    // ID file baik yang diawali /d/ maupun files/
    $regexFileIdGdrive = '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/';
    preg_match($regexFileIdGdrive, $mediaUrl, $matches);
    $fileIdFromUrl = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : null);

    $fileId = $fileIdFromUrl ?? null;
    $uploader = $music['uploader'] ?? null;
    $isSuspicious = filter_var(
        $music['is_suspicious'] ?? false,
        FILTER_VALIDATE_BOOLEAN
    );

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

    // Jika bukan file suspicious, pakai dari wahabinasrul
    if (!$isSuspicious) {
        $uploader = "wahabinasrul@gmail.com";
    } else {
        log_message("[WARNING] File is suspicious, get refresh token from owner.");
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

    $isCacheValid = file_exists($cacheFilePath)
        && (time() - filemtime($cacheFilePath) < $cacheDuration);

    $cacheCreated = false;

    if (!$isCacheValid) {

        log_message("[INFO] Cache MISS for fileId: $fileId.");

        // Gunakan file lock terpisah.
        // Jangan lock langsung ke cacheFilePath karena file tersebut
        // nantinya akan diganti menggunakan rename().
        $lockFilePath = $cacheFilePath . '.lock';

        $lockFp = fopen($lockFilePath, 'c');

        if (!$lockFp) {
            http_response_code(500);
            log_message("[ERROR] Could not open lock file: $lockFilePath");
            die("Could not open lock file.");
        }

        if (!flock($lockFp, LOCK_EX)) {
            fclose($lockFp);

            http_response_code(503);
            log_message("[ERROR] Could not get cache lock.");
            die("Could not get cache lock.");
        }

        // =========================================================
        // PENTING:
        // Cek ulang setelah mendapatkan lock.
        // Request lain mungkin sudah selesai membuat cache.
        // =========================================================

        $isCacheValid = file_exists($cacheFilePath)
            && (time() - filemtime($cacheFilePath) < $cacheDuration);

        if ($isCacheValid) {

            log_message(
                "[INFO] Cache was created by another process. "
                . "Skip download for fileId: $fileId."
            );

        } else {

            log_message(
                "[INFO] Downloading fileId: $fileId from Google Drive."
            );

            $tokenData = getGdriveOauthToken($config, $isSuspicious);
            $accessToken = $tokenData['access_token'];

            $driveUrl =
                "https://www.googleapis.com/drive/v3/files/"
                . $fileId
                . "?alt=media";

            if ($isSuspicious) {
                $driveUrl .= "&acknowledgeAbuse=true";
            }

            $tempFilePath =
                $cacheFilePath
                . '.tmp.'
                . getmypid();

            $tempFp = fopen($tempFilePath, 'wb');

            if (!$tempFp) {

                flock($lockFp, LOCK_UN);
                fclose($lockFp);

                http_response_code(500);
                log_message(
                    "[ERROR] Could not create temporary cache file: "
                    . $tempFilePath
                );

                die("Could not create temporary cache file.");
            }

            // ---------------------------------------------------------
            // Download Google Drive -> temporary file
            // ---------------------------------------------------------

            $ch = curl_init($driveUrl);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $accessToken
            ]);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FILE, $tempFp);

            $result = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            log_message(
                "[GDRIVE DEBUG] result=" . var_export($result, true)
                . " HTTP=" . $httpCode
                . " error=" . $curlError
            );

            unset($ch);
            fclose($tempFp);

            // ---------------------------------------------------------
            // Validasi download
            // ---------------------------------------------------------

            if (
                $result === false ||
                $httpCode < 200 ||
                $httpCode >= 300
            ) {

                log_message(
                    "[ERROR] GDrive download failed. "
                    . "HTTP: $httpCode Error: $curlError"
                );

                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }

                flock($lockFp, LOCK_UN);
                fclose($lockFp);

                http_response_code(500);
                die("Failed to download file from Google Drive.");
            }

            // ---------------------------------------------------------
            // Download sudah lengkap.
            // Atomic replace:
            // temp -> cache resmi
            // ---------------------------------------------------------

            if (!rename($tempFilePath, $cacheFilePath)) {

                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }

                flock($lockFp, LOCK_UN);
                fclose($lockFp);

                http_response_code(500);
                die("Failed to finalize cache file.");
            }

            $cacheCreated = true;

            log_message(
                "[SUCCESS] Cache created for fileId: $fileId."
            );
        }

        // Lepaskan lock
        flock($lockFp, LOCK_UN);
        fclose($lockFp);

    } else {

        log_message(
            "[INFO] Cache HIT for fileId: $fileId. "
            . "Serving from local server."
        );
    }

    if ($fileType == "audio") {
        // echo json_encode($responsePayload);
        outputJson([
            "success" => true,
            "music_id" => $musicId,
            "stream_url" => $cacheFileUrl,
        ]);

        finishResponse();

        // JALANKAN PROSES LATAR BELAKANG ---
        // Script PHP masih jalan di server, tapi user sudah tidak menunggu (loading icon di browser sudah hilang)
        if ($cacheCreated) {
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
// Hati-hati sama exit di sini.
// Jika file ini di-include ke file lain, exit() akan menghentikan eksekusi file tersebut.
// Karena exit di sini tidak di dalam function, maka exit() akan menghentikan eksekusi file yang meng-include file ini.
// exit();