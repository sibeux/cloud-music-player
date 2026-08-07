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

    $regexFileIdGdrive =
        '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/';

    preg_match(
        $regexFileIdGdrive,
        $mediaUrl,
        $matches
    );

    $fileId =
        !empty($matches[1])
        ? $matches[1]
        : (
            !empty($matches[2])
            ? $matches[2]
            : null
        );


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

    $isSuspicious = filter_var(
        $music['is_suspicious'] ?? false,
        FILTER_VALIDATE_BOOLEAN
    );


    // File normal menggunakan account utama
    if (!$isSuspicious) {

        $uploader = "wahabinasrul@gmail.com";

    } else {

        log_message(
            "[WARNING] File is suspicious. "
            . "Using owner's Google Drive credentials."
        );
    }


    // =========================================================
    // CACHE CONFIG
    // =========================================================

    $cacheDir =
        __DIR__ .
        '/../database/mobile-music-player/api/music-host';

    $cacheUrl =
        $_ENV['CACHE_FILE_URL'] ?? null;


    if (!$cacheUrl) {

        http_response_code(500);

        outputJson([
            "status" => "error",
            "message" => "CACHE_FILE_URL belum disetting."
        ]);

        return;
    }


    // Cache 1 tahun
    $cacheDuration = 31536000;


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

    $cacheFilePath =
        $cacheDir .
        '/' .
        $safeFileId;

    $cacheFileUrl =
        rtrim($cacheUrl, '/') .
        '/' .
        $safeFileId;


    // =========================================================
    // 1. CEK DATABASE CACHE
    // =========================================================

    $isCachedInDb = false;

    $stmt = $db->prepare("
        SELECT 1
        FROM cache_musics
        WHERE cache_music_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $musicId
        );

        if ($stmt->execute()) {

            $result = $stmt->get_result();

            $isCachedInDb =
                $result->num_rows > 0;
        }

        $stmt->close();
    }


    // =========================================================
    // 2. CEK FILE CACHE
    // =========================================================

    $isCacheFileValid =
        file_exists($cacheFilePath)
        &&
        is_file($cacheFilePath)
        &&
        filesize($cacheFilePath) > 0
        &&
        (
            time() - filemtime($cacheFilePath)
            <
            $cacheDuration
        );


    log_message(
        "[CACHE CHECK] "
        . "musicId=$musicId "
        . "fileId=$fileId "
        . "db=" . ($isCachedInDb ? "YES" : "NO")
        . " file=" . ($isCacheFileValid ? "YES" : "NO")
    );


    // =========================================================
    // CACHE HIT
    // =========================================================

    if (
        $isCachedInDb
        &&
        $isCacheFileValid
    ) {

        log_message(
            "[CACHE HIT] "
            . "musicId=$musicId "
            . "fileId=$fileId"
        );


        if ($fileType === "audio") {

            outputJson([
                "success" => true,
                "music_id" => $musicId,
                "cached" => true,
                "stream_url" => $cacheFileUrl,
            ]);

            return;
        }


        header(
            "Location: " . $cacheFileUrl,
            true,
            302
        );

        return;
    }


    // =========================================================
    // CACHE MISS
    // =========================================================

    log_message(
        "[CACHE MISS] "
        . "musicId=$musicId "
        . "fileId=$fileId"
    );


    // =========================================================
    // GOOGLE OAUTH
    // =========================================================

    $config =
        getGoogleDriveCredentials(
            $uploader,
            $allApiData
        );


    $tokenData =
        getGdriveOauthToken(
            $config,
            $isSuspicious
        );


    if (
        empty($tokenData['access_token'])
    ) {

        http_response_code(500);

        outputJson([
            "status" => "error",
            "message" =>
                "Failed to get Google Drive access token."
        ]);

        return;
    }


    $accessToken =
        $tokenData['access_token'];


    // =========================================================
    // GOOGLE DRIVE URL
    // =========================================================

    $driveUrl =
        "https://www.googleapis.com/drive/v3/files/"
        . rawurlencode($fileId)
        . "?alt=media";


    if ($isSuspicious) {

        $driveUrl .=
            "&acknowledgeAbuse=true";
    }


    // =========================================================
    // LOCK FILE
    // =========================================================

    $lockFilePath =
        $cacheFilePath . '.lock';


    $lockFp =
        fopen(
            $lockFilePath,
            'c'
        );


    if (!$lockFp) {

        log_message(
            "[ERROR] Cannot create lock file: "
            . $lockFilePath
        );

        // Tetap berikan GDrive URL ke user
        returnGoogleDriveUrl(
            $musicId,
            $fileType,
            $driveUrl
        );

        return;
    }


    // =========================================================
    // NON-BLOCKING LOCK
    //
    // Kalau ada proses lain sedang download,
    // jangan menunggu.
    // =========================================================

    $lockAcquired =
        flock(
            $lockFp,
            LOCK_EX | LOCK_NB
        );


    // =========================================================
    // REQUEST LAIN SEDANG DOWNLOAD
    // =========================================================

    if (!$lockAcquired) {

        log_message(
            "[CACHE IN PROGRESS] "
            . "Another process is downloading "
            . "musicId=$musicId"
        );

        fclose($lockFp);


        // User tetap langsung stream dari GDrive
        returnGoogleDriveUrl(
            $musicId,
            $fileType,
            $driveUrl
        );

        return;
    }


    // =========================================================
    // DOUBLE CHECK SETELAH DAPAT LOCK
    //
    // Bisa saja request lain selesai tepat sebelum kita
    // mendapatkan lock.
    // =========================================================

    $isCacheFileValid =
        file_exists($cacheFilePath)
        &&
        is_file($cacheFilePath)
        &&
        filesize($cacheFilePath) > 0
        &&
        (
            time() - filemtime($cacheFilePath)
            <
            $cacheDuration
        );


    if ($isCacheFileValid) {

        log_message(
            "[CACHE CREATED BY OTHER PROCESS] "
            . "musicId=$musicId"
        );

        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);


        if ($fileType === "audio") {

            outputJson([
                "success" => true,
                "music_id" => $musicId,
                "cached" => true,
                "stream_url" => $cacheFileUrl,
            ]);

            return;
        }


        header(
            "Location: " . $cacheFileUrl,
            true,
            302
        );

        return;
    }


    // =========================================================
    // USER RESPONSE DULU
    //
    // User langsung mendapatkan Google Drive URL.
    // Setelah response selesai, kita coba lanjutkan
    // proses download di background.
    // =========================================================

    if (
        function_exists(
            'fastcgi_finish_request'
        )
    ) {

        log_message(
            "[BG] Sending Google Drive URL to user. "
            . "musicId=$musicId"
        );


        if ($fileType === "audio") {

            outputJson([
                "success" => true,
                "music_id" => $musicId,
                "cached" => false,
                "stream_url" => $driveUrl,
            ]);

        } else {

            header(
                "Location: " . $driveUrl,
                true,
                302
            );
        }


        log_message(
            "[BG] BEFORE fastcgi_finish_request"
        );


        fastcgi_finish_request();


        log_message(
            "[BG] AFTER fastcgi_finish_request"
        );

    } else {

        // =====================================================
        // FASTCGI TIDAK TERSEDIA
        //
        // Jangan download dalam request user.
        // Berikan GDrive URL saja.
        // =====================================================

        log_message(
            "[WARNING] fastcgi_finish_request() "
            . "is not available. "
            . "Skipping background download."
        );


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);


        returnGoogleDriveUrl(
            $musicId,
            $fileType,
            $driveUrl
        );

        return;
    }


    // =========================================================
    // DOWNLOAD GOOGLE DRIVE -> TEMP FILE
    // =========================================================

    $tempFilePath =
        $cacheFilePath
        . '.tmp.'
        . getmypid();


    log_message(
        "[BG] Creating temp file: "
        . $tempFilePath
    );


    $tempFp =
        fopen(
            $tempFilePath,
            'wb'
        );


    if (!$tempFp) {

        log_message(
            "[ERROR] Cannot create temp file: "
            . $tempFilePath
        );


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);

        return;
    }


    // =========================================================
    // CURL DOWNLOAD
    // =========================================================

    log_message(
        "[BG] Starting Google Drive download. "
        . "musicId=$musicId "
        . "fileId=$fileId"
    );


    $ch =
        curl_init(
            $driveUrl
        );


    curl_setopt_array(
        $ch,
        [
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $accessToken
            ],

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_HEADER => false,

            CURLOPT_FILE => $tempFp,

            CURLOPT_CONNECTTIMEOUT => 15,

            // Tidak dibatasi waktu
            CURLOPT_TIMEOUT => 0,

            CURLOPT_FAILONERROR => false,

            CURLOPT_RETURNTRANSFER => false,

            CURLOPT_SSL_VERIFYPEER => true,

            CURLOPT_SSL_VERIFYHOST => 2,
        ]
    );


    $result =
        curl_exec($ch);


    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );


    $downloadSize =
        curl_getinfo(
            $ch,
            CURLINFO_SIZE_DOWNLOAD
        );


    $curlError =
        curl_error($ch);


    $curlErrno =
        curl_errno($ch);


    curl_close($ch);

    fclose($tempFp);


    // =========================================================
    // CURL DEBUG
    // =========================================================

    log_message(
        "[BG DEBUG] CURL FINISHED. "
        . "result="
        . var_export($result, true)
        . " HTTP=$httpCode"
        . " errno=$curlErrno"
        . " error=$curlError"
        . " downloaded=$downloadSize bytes"
    );


    // =========================================================
    // VALIDASI DOWNLOAD
    // =========================================================

    if (
        $result === false
        ||
        $httpCode < 200
        ||
        $httpCode >= 300
    ) {

        log_message(
            "[ERROR] GDrive background download failed. "
            . "HTTP=$httpCode "
            . "errno=$curlErrno "
            . "error=$curlError"
        );


        if (
            file_exists($tempFilePath)
        ) {

            unlink(
                $tempFilePath
            );
        }


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);

        return;
    }


    // =========================================================
    // VALIDASI FILE TEMP
    // =========================================================

    clearstatcache(
        true,
        $tempFilePath
    );


    $tempFileSize =
        file_exists($tempFilePath)
        ? filesize($tempFilePath)
        : 0;


    log_message(
        "[BG] Temp file size: "
        . $tempFileSize
        . " bytes"
    );


    if (
        !file_exists($tempFilePath)
        ||
        $tempFileSize <= 0
    ) {

        log_message(
            "[ERROR] Download returned success "
            . "but temp file is empty."
        );


        if (
            file_exists($tempFilePath)
        ) {

            unlink(
                $tempFilePath
            );
        }


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);

        return;
    }


    // =========================================================
    // ATOMIC RENAME
    // =========================================================

    log_message(
        "[BG] Attempting atomic rename: "
        . $tempFilePath
        . " -> "
        . $cacheFilePath
    );


    if (
        !rename(
            $tempFilePath,
            $cacheFilePath
        )
    ) {

        log_message(
            "[ERROR] Failed to rename temp cache file."
        );


        if (
            file_exists($tempFilePath)
        ) {

            unlink(
                $tempFilePath
            );
        }


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);

        return;
    }


    log_message(
        "[BG] Rename SUCCESS."
    );


    // =========================================================
    // VALIDASI CACHE FINAL
    // =========================================================

    clearstatcache(
        true,
        $cacheFilePath
    );


    if (
        !file_exists($cacheFilePath)
        ||
        filesize($cacheFilePath) <= 0
    ) {

        log_message(
            "[ERROR] Cache file does not exist "
            . "after rename."
        );


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);

        return;
    }


    // =========================================================
    // INSERT DB CACHE
    // =========================================================

    log_message(
        "[BG] BEFORE DB CACHE INSERT"
    );


    sendToSqlCache(
        $db,
        $fileId,
        $musicId
    );


    log_message(
        "[BG] AFTER DB CACHE INSERT"
    );


    // =========================================================
    // CHECK CODEC
    // =========================================================

    if ($fileType === "audio") {

        log_message(
            "[BG] Starting codec check. "
            . "musicId=$musicId"
        );


        checkCodecAudio(
            $musicId,
            $cacheFilePath,
            $db,
            $ffprobePath
        );


        log_message(
            "[BG] Codec check finished. "
            . "musicId=$musicId"
        );
    }


    // =========================================================
    // SELESAI
    // =========================================================

    log_message(
        "[CACHE SUCCESS] "
        . "musicId=$musicId "
        . "fileId=$fileId "
        . "size="
        . filesize($cacheFilePath)
        . " bytes"
    );


    flock(
        $lockFp,
        LOCK_UN
    );

    fclose($lockFp);

    return;
}


// =============================================================
// RETURN GOOGLE DRIVE URL
// =============================================================

function returnGoogleDriveUrl(
    $musicId,
    $fileType,
    $driveUrl
) {
    if ($fileType === "audio") {

        outputJson([
            "success" => true,
            "music_id" => $musicId,
            "cached" => false,
            "stream_url" => $driveUrl,
        ]);

        return;
    }


    header(
        "Location: " . $driveUrl,
        true,
        302
    );
}


// =============================================================
// INSERT CACHE DATABASE
// =============================================================

function sendToSqlCache(
    $db,
    $fileId,
    $musicId
) {
    $stmt = $db->prepare("
        INSERT INTO cache_musics (
            cache_music_id
        )
        SELECT ?
        WHERE NOT EXISTS (
            SELECT 1
            FROM cache_musics
            WHERE cache_music_id = ?
        )
    ");


    if (!$stmt) {

        log_message(
            "[ERROR] Failed to prepare cache DB query: "
            . $db->error
        );

        return;
    }


    $stmt->bind_param(
        "ii",
        $musicId,
        $musicId
    );


    if (!$stmt->execute()) {

        log_message(
            "[ERROR] Failed inserting cache_musics: "
            . $stmt->error
        );

    } else {

        log_message(
            "[SUCCESS] Cache DB updated. "
            . "fileId=$fileId "
            . "musicId=$musicId"
        );
    }


    $stmt->close();
}