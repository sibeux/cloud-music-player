<?php

require_once __DIR__ . '/../utils/utils.php';
require_once __DIR__ . '/image-dominant-color/get_color.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';
require_once __DIR__ . '/music/stream/get_gdrive_oauth_token.php';
require_once __DIR__ . '/google-oauth-config.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

function streamingMusicFromGdrive(
    $db,
    $musicId,
    $mediaUrl,
    $fileType,
    $allApiData,
    $ffprobePath
) {
    // =========================================================
    // VALIDASI
    // =========================================================

    if (!$musicId) {
        http_response_code(400);

        outputJson([
            "status" => "error",
            "error" => "musicId_not_found",
            "message" => "Music ID not found",
        ]);

        return;
    }

    // =========================================================
    // AMBIL DATA MUSIC
    // =========================================================

    $music = [];

    if ($fileType !== "image") {
        $stmt = $db->prepare("
            SELECT
                m.uploader,
                m.is_suspicious
            FROM musics m
            WHERE m.id_music = ?
            LIMIT 1
        ");

        if (!$stmt) {
            http_response_code(500);
            outputJson([
                "status" => "error",
                "message" => "Failed to prepare music query."
            ]);
            return;
        }

        $stmt->bind_param("i", $musicId);

        if (!$stmt->execute()) {
            $stmt->close();
            http_response_code(500);
            outputJson([
                "status" => "error",
                "message" => "Failed to execute music query."
            ]);
            return;
        }

        $result = $stmt->get_result();
        $music = $result->fetch_assoc() ?: [];
        $stmt->close();
    }

    // =========================================================
    // AMBIL GOOGLE DRIVE FILE ID
    // =========================================================

    $regexFileIdGdrive = '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/';
    preg_match($regexFileIdGdrive, $mediaUrl, $matches);

    $fileId = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : null);

    if (!$fileId) {
        http_response_code(400);
        outputJson([
            "status" => "error",
            "error" => "fileId_not_found",
            "message" => "Google Drive File ID not found",
        ]);
        return;
    }

    // =========================================================
    // SUSPICIOUS FLAG
    // =========================================================

    $uploader = $music['uploader'] ?? null;
    $isSuspicious = filter_var($music['is_suspicious'] ?? false, FILTER_VALIDATE_BOOLEAN);

    // File normal menggunakan account utama
    if (!$isSuspicious) {
        $uploader = "wahabinasrul@gmail.com";
    } else {
        log_message("[WARNING] File is suspicious. Using owner's Google Drive credentials.");
    }

    // =========================================================
    // CACHE CONFIG
    // =========================================================

    $cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host';
    $cacheUrl = $_ENV['CACHE_FILE_URL'] ?? null;

    if (!$cacheUrl) {
        http_response_code(500);
        outputJson([
            "status" => "error",
            "message" => "CACHE_FILE_URL belum disetting."
        ]);
        return;
    }

    $cacheDuration = 31536000; // Cache 1 tahun

    // =========================================================
    // PASTIKAN CACHE DIRECTORY ADA
    // =========================================================

    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true)) {
            http_response_code(500);
            outputJson([
                "status" => "error",
                "message" => "Failed to create cache directory."
            ]);
            return;
        }
    }

    // =========================================================
    // CACHE PATH
    // =========================================================

    $safeFileId = basename($fileId);
    $cacheFilePath = $cacheDir . '/' . $safeFileId;
    $cacheFileUrl = rtrim($cacheUrl, '/') . '/' . $safeFileId;

    // =========================================================
    // 1. CEK DATABASE CACHE
    // =========================================================

    $isCachedInDb = false;
    $stmt = $db->prepare("SELECT 1 FROM cache_musics WHERE cache_music_id = ? LIMIT 1");

    if ($stmt) {
        $stmt->bind_param("i", $musicId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $isCachedInDb = $result->num_rows > 0;
        }
        $stmt->close();
    }

    // =========================================================
    // 2. CEK FILE CACHE
    // =========================================================

    $isCacheFileValid = file_exists($cacheFilePath) && is_file($cacheFilePath) && filesize($cacheFilePath) > 0 && (time() - filemtime($cacheFilePath) < $cacheDuration);

    log_message("[CACHE CHECK] musicId=$musicId fileId=$fileId db=" . ($isCachedInDb ? "YES" : "NO") . " file=" . ($isCacheFileValid ? "YES" : "NO"));

    // =========================================================
    // CACHE HIT
    // =========================================================

    if ($isCachedInDb && $isCacheFileValid) {
        log_message("[CACHE HIT] musicId=$musicId fileId=$fileId");

        if ($fileType === "audio") {
            outputJson([
                "success" => true,
                "music_id" => $musicId,
                "cached" => true,
                "stream_url" => $cacheFileUrl,
            ]);
            return;
        }

        header("Location: " . $cacheFileUrl, true, 302);
        return;
    }

    // =========================================================
    // CACHE MISS
    // =========================================================

    log_message("[CACHE MISS] musicId=$musicId fileId=$fileId");

    // =========================================================
    // GOOGLE OAUTH
    // =========================================================

    $config = getGoogleDriveCredentials($uploader, $allApiData);
    $tokenData = getGdriveOauthToken($config, $isSuspicious);

    if (empty($tokenData['access_token'])) {
        http_response_code(500);
        outputJson([
            "status" => "error",
            "message" => "Failed to get Google Drive access token."
        ]);
        return;
    }

    // =========================================================
    // GOOGLE DRIVE URL
    // =========================================================

    $driveUrl = "https://www.googleapis.com/drive/v3/files/" . rawurlencode($fileId) . "?alt=media";
    if ($isSuspicious) {
        $driveUrl .= "&acknowledgeAbuse=true";
    }

    // =========================================================
    // NON-BLOCKING LOCK CHECK
    // =========================================================
    
    $lockFilePath = $cacheFilePath . '.lock';
    $isLocked = false;
    
    if (file_exists($lockFilePath)) {
        // Jika file lock ada, mungkin proses lain sedang berjalan. Cek sebentar.
        $lockFp = @fopen($lockFilePath, 'c');
        if ($lockFp) {
            if (!flock($lockFp, LOCK_EX | LOCK_NB)) {
                $isLocked = true;
            } else {
                flock($lockFp, LOCK_UN);
            }
            fclose($lockFp);
        }
    }

    if ($isLocked) {
        log_message("[CACHE IN PROGRESS] Another process is downloading musicId=$musicId");
    } else {
        log_message("[CACHE WORKER START] Triggering worker for musicId=$musicId fileId=$fileId");
        triggerCacheWorkerBackground($musicId, $fileId, $fileType, $ffprobePath);
    }

    // =========================================================
    // RETURN USER RESPONSE (GOOGLE DRIVE DIRECT URL)
    // =========================================================
    
    returnGoogleDriveUrl($musicId, $fileType, $driveUrl);
}

// =============================================================
// TRIGGER WORKER BACKGROUND TANPA FASTCGI
// =============================================================

function triggerCacheWorkerBackground($musicId, $fileId, $fileType, $ffprobePath) {
    // Kita gunakan CURL asinkron (fire and forget) dengan timeout yang sangat kecil (200ms)
    // Ini sangat handal di cPanel karena tidak bergantung pada eksekusi command line.
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    
    if (isset($_SERVER['HTTP_HOST'])) {
        // Resolve absolute URL to cacheMusicWorker.php dynamically
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $currentDir = str_replace('\\', '/', __DIR__);
        
        $basePath = str_replace($docRoot, '', $currentDir);
        $workerUrl = rtrim($protocol . "://" . $_SERVER['HTTP_HOST'] . $basePath, '/') . '/cacheMusicWorker.php';
        
        $ch = curl_init($workerUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'musicId' => $musicId,
            'fileId' => $fileId,
            'fileType' => $fileType,
            'ffprobePath' => $ffprobePath
        ]));
        // Gunakan timeout 1 detik. 200ms mungkin terlalu cepat untuk SSL handshake di cPanel
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); 
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Disable SSL verify agar tidak gagal pada localhost/self-signed
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $curlResult = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        log_message("[BG] Triggered worker via cURL to: " . $workerUrl . " | HTTP: " . $httpCode . " | Err: " . $curlErr);
    } else {
        // Fallback jika bukan request via web (misal CLI) - menggunakan exec 
        $cmd = "php " . escapeshellarg(__DIR__ . "/cacheMusicWorker.php") . " " 
             . escapeshellarg($musicId) . " " . escapeshellarg($fileId) . " " 
             . escapeshellarg($fileType) . " " . escapeshellarg($ffprobePath) 
             . " > /dev/null 2>&1 &";
             
        if (function_exists('exec')) {
            exec($cmd);
            log_message("[BG] Triggered worker via CLI exec");
        }
    }
}

// =============================================================
// RETURN GOOGLE DRIVE URL
// =============================================================

function returnGoogleDriveUrl($musicId, $fileType, $driveUrl) {
    if ($fileType === "audio") {
        outputJson([
            "success" => true,
            "music_id" => $musicId,
            "cached" => false,
            "stream_url" => $driveUrl,
        ]);
        return;
    }

    header("Location: " . $driveUrl, true, 302);
}