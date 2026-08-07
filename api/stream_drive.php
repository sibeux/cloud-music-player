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

    $query = "
        SELECT
            m.uploader,
            m.is_suspicious
        FROM musics m
        WHERE m.id_music = ?
    ";

    $music = [];

    if ($fileType !== "image") {

        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $musicId);
        $stmt->execute();

        $result = $stmt->get_result();
        $music = $result->fetch_assoc();

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

    $fileIdFromUrl =
        !empty($matches[1])
        ? $matches[1]
        : (
            !empty($matches[2])
            ? $matches[2]
            : null
        );

    $fileId = $fileIdFromUrl;


    if (!$fileId) {

        http_response_code(400);

        outputJson([
            "status" => "error",
            "error" => "fileId_not_found",
            "message" => "File ID not found",
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


    // Kalau bukan suspicious, gunakan account utama
    if (!$isSuspicious) {

        $uploader = "wahabinasrul@gmail.com";

    } else {

        log_message(
            "[WARNING] File is suspicious, get refresh token from owner."
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


    $cacheDuration = 31536000; // 1 tahun


    // Pastikan directory ada
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


    $cacheFilePath =
        $cacheDir .
        '/' .
        basename($fileId);


    $cacheFileUrl =
        rtrim($cacheUrl, '/') .
        '/' .
        basename($fileId);


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

    $stmt->bind_param("i", $musicId);
    $stmt->execute();

    $result = $stmt->get_result();

    $isCachedInDb = $result->num_rows > 0;

    $stmt->close();


    // =========================================================
    // 2. CEK FILE CACHE
    // =========================================================

    $isCacheFileValid =
        file_exists($cacheFilePath)
        &&
        (time() - filemtime($cacheFilePath) < $cacheDuration);


    // =========================================================
    // CACHE SUDAH ADA
    // =========================================================

    if ($isCachedInDb && $isCacheFileValid) {

        log_message(
            "[CACHE HIT] musicId=$musicId fileId=$fileId"
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
    // CACHE BELUM ADA
    // =========================================================

    log_message(
        "[CACHE MISS] musicId=$musicId fileId=$fileId"
    );


    // =========================================================
    // DAPATKAN GOOGLE OAUTH TOKEN
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
            "message" => "Failed to get Google Drive access token."
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
        . "?alt=media"
        . "&access_token="
        . rawurlencode($accessToken);


    if ($isSuspicious) {

        $driveUrl .=
            "&acknowledgeAbuse=true";
    }


    // =========================================================
    // LOCK
    //
    // Tujuannya:
    // User A -> mulai download
    // User B -> tidak download ulang
    // User C -> tidak download ulang
    // =========================================================

    $lockFilePath =
        $cacheFilePath . '.lock';

    $lockFp =
        fopen($lockFilePath, 'c');


    if (!$lockFp) {

        http_response_code(500);

        outputJson([
            "status" => "error",
            "message" => "Could not create cache lock."
        ]);

        return;
    }


    // NON-BLOCKING LOCK
    //
    // Kalau ada proses lain yang sedang download,
    // kita tidak perlu menunggu.
    $lockAcquired =
        flock(
            $lockFp,
            LOCK_EX | LOCK_NB
        );


    if ($lockAcquired) {

        log_message(
            "[CACHE DOWNLOAD] Starting background download "
            . "musicId=$musicId fileId=$fileId"
        );


        // =====================================================
        // START BACKGROUND DOWNLOAD
        //
        // fastcgi_finish_request() membuat response dikirim
        // ke client terlebih dahulu.
        // =====================================================

        if (
            function_exists('fastcgi_finish_request')
        ) {

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


            fastcgi_finish_request();


            // =================================================
            // DOWNLOAD GOOGLE DRIVE -> TEMP FILE
            // =================================================

            $tempFilePath =
                $cacheFilePath .
                '.tmp.' .
                getmypid();


            $tempFp =
                fopen(
                    $tempFilePath,
                    'wb'
                );


            if (!$tempFp) {

                log_message(
                    "[ERROR] Could not create temp file: "
                    . $tempFilePath
                );

                flock($lockFp, LOCK_UN);
                fclose($lockFp);

                return;
            }


            $ch =
                curl_init($driveUrl);


            curl_setopt_array(
                $ch,
                [
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_FILE => $tempFp,

                    CURLOPT_CONNECTTIMEOUT => 15,
                    CURLOPT_TIMEOUT => 0,

                    CURLOPT_FAILONERROR => false,
                ]
            );


            $result =
                curl_exec($ch);


            $httpCode =
                curl_getinfo(
                    $ch,
                    CURLINFO_HTTP_CODE
                );


            $curlError =
                curl_error($ch);


            curl_close($ch);

            fclose($tempFp);


            // =================================================
            // VALIDASI DOWNLOAD
            // =================================================

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
                    . "Error=$curlError"
                );


                if (
                    file_exists($tempFilePath)
                ) {

                    unlink($tempFilePath);
                }


                flock($lockFp, LOCK_UN);
                fclose($lockFp);

                return;
            }


            // =================================================
            // ATOMIC REPLACE
            // =================================================

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

                    unlink($tempFilePath);
                }


                flock($lockFp, LOCK_UN);
                fclose($lockFp);

                return;
            }


            // =================================================
            // INSERT DATABASE CACHE
            // =================================================

            sendToSqlCache(
                $db,
                $fileId,
                $musicId
            );


            log_message(
                "[CACHE SUCCESS] "
                . "musicId=$musicId "
                . "fileId=$fileId"
            );


            // =================================================
            // CHECK CODEC
            // =================================================

            if ($fileType === "audio") {

                checkCodecAudio(
                    $musicId,
                    $cacheFilePath,
                    $db,
                    $ffprobePath
                );
            }


            flock(
                $lockFp,
                LOCK_UN
            );

            fclose($lockFp);

            return;
        }


        // =====================================================
        // FASTCGI TIDAK TERSEDIA
        // =====================================================

        log_message(
            "[WARNING] fastcgi_finish_request() "
            . "not available."
        );


        flock(
            $lockFp,
            LOCK_UN
        );

        fclose($lockFp);


    } else {

        // =====================================================
        // ADA REQUEST LAIN YANG SEDANG DOWNLOAD
        // =====================================================

        log_message(
            "[CACHE DOWNLOAD IN PROGRESS] "
            . "musicId=$musicId"
        );


        fclose($lockFp);
    }


    // =========================================================
    // KARENA CACHE BELUM READY,
    // USER LANGSUNG STREAM DARI GOOGLE DRIVE
    // =========================================================

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

    // Hindari duplicate insert
    $stmt = $db->prepare("
        INSERT INTO cache_musics (cache_music_id)
        SELECT ?
        WHERE NOT EXISTS (
            SELECT 1
            FROM cache_musics
            WHERE cache_music_id = ?
        )
    ");


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
            . "fileId=$fileId musicId=$musicId"
        );
    }


    $stmt->close();
}
