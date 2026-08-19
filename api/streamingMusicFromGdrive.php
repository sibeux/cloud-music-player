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
    // CACHE CONFIG
    // =========================================================

    $cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host';
    $safeFileId = basename($fileId);
    $cacheFilePath = $cacheDir . '/' . $safeFileId;

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

    $cacheDuration = 31536000;
    $isCacheFileValid = file_exists($cacheFilePath) && is_file($cacheFilePath) && filesize($cacheFilePath) > 0 && (time() - filemtime($cacheFilePath) < $cacheDuration);

    $isCached = ($isCachedInDb && $isCacheFileValid);

    // =========================================================
    // RETURN ENDPOINT URL
    // =========================================================
    
    $streamEndpointUrl = getApiUrl('stream-gdrive.php?music_id=' . urlencode($musicId));
    
    if ($fileType === "audio") {
        outputJson([
            "success" => true,
            "music_id" => $musicId,
            "cached" => $isCached,
            "stream_url" => $streamEndpointUrl
        ]);
        return;
    }

    // Fallback if not audio (e.g. image)
    // Langsung proxy file gambar ke klien (menghindari Captcha dari Google Drive)
    $uploader = "wahabinasrul@gmail.com";
    $config = getGoogleDriveCredentials($uploader, $allApiData);
    $tokenData = getGdriveOauthToken($config, false);
    $accessToken = $tokenData['access_token'] ?? '';
    
    $driveUrl = "https://www.googleapis.com/drive/v3/files/" . rawurlencode($fileId) . "?alt=media";
    
    $ch = curl_init($driveUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $accessToken]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) {
        $lower = strtolower(trim($header));
        if (strpos($lower, 'content-type:') === 0 || strpos($lower, 'content-length:') === 0) {
            header(trim($header));
        }
        return strlen($header);
    });
    
    curl_exec($ch);
    unset($ch);
}
