<?php
// Izinkan origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Range");
header("Access-Control-Expose-Headers: Content-Range, Content-Length, Accept-Ranges");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../utils/utils.php';
require_once __DIR__ . '/music/stream/get_gdrive_oauth_token.php';
require_once __DIR__ . '/google-oauth-config.php'; // Menyediakan $allApiData

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$musicId = isset($_GET['music_id']) ? $_GET['music_id'] : null;

if (!$musicId) {
    http_response_code(400);
    echo "Music ID is required";
    exit;
}

// 1. Ambil data music dari DB
$stmt = $db->prepare("SELECT m.link_gdrive, m.uploader, m.is_suspicious FROM musics m WHERE m.id_music = ? LIMIT 1");
$stmt->bind_param("i", $musicId);
$stmt->execute();
$music = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$music || empty($music['link_gdrive'])) {
    http_response_code(404);
    echo "Music not found";
    exit;
}

$regexFileIdGdrive = '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/';
preg_match($regexFileIdGdrive, $music['link_gdrive'], $matches);
$fileId = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : null);

if (!$fileId) {
    http_response_code(400);
    echo "Invalid Google Drive URL";
    exit;
}

// 2. Cache Config
$cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host';
$safeFileId = basename($fileId);
$cacheFilePath = $cacheDir . '/' . $safeFileId;

// Pastikan direktori cache ada
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Cek cache
$cacheDuration = 31536000; // 1 tahun
$isCacheFileValid = file_exists($cacheFilePath) && filesize($cacheFilePath) > 0 && (time() - filemtime($cacheFilePath) < $cacheDuration);

$isCachedInDb = false;
$stmt = $db->prepare("SELECT 1 FROM cache_musics WHERE cache_music_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $musicId);
    if ($stmt->execute()) {
        $isCachedInDb = $stmt->get_result()->num_rows > 0;
    }
    $stmt->close();
}

$isCached = $isCachedInDb && $isCacheFileValid;

// 3. JIKA SUDAH CACHE -> Stream dari file lokal
if ($isCached) {
    log_message("[STREAM CACHE HIT] musicId=$musicId fileId=$fileId");
    serveLocalFileWithRange($cacheFilePath, 'audio/mpeg');
    exit;
}

// 4. JIKA BELUM CACHE -> Stream dari Google Drive
log_message("[STREAM CACHE MISS] musicId=$musicId fileId=$fileId");

// Siapkan OAuth
$uploader = $music['uploader'] ?? null;
$isSuspicious = filter_var($music['is_suspicious'] ?? false, FILTER_VALIDATE_BOOLEAN);
if (!$isSuspicious) {
    $uploader = "wahabinasrul@gmail.com";
}

$config = getGoogleDriveCredentials($uploader, $allApiData);
$tokenData = getGdriveOauthToken($config, $isSuspicious);

if (empty($tokenData['access_token'])) {
    http_response_code(500);
    log_message("[STREAM GDRIVE ERROR] Failed to get Google Drive access token for musicId=$musicId");
    echo "Failed to get Google Drive access token.";
    exit;
}

$accessToken = $tokenData['access_token'];
$driveUrl = "https://www.googleapis.com/drive/v3/files/" . rawurlencode($fileId) . "?alt=media";
if ($isSuspicious) {
    $driveUrl .= "&acknowledgeAbuse=true";
}

$isPartialRequest = isset($_SERVER['HTTP_RANGE']);
$rangeHeader = $isPartialRequest ? $_SERVER['HTTP_RANGE'] : null;

$shouldCache = false;
if (!$isPartialRequest || preg_match('/^bytes=0-$/', trim($rangeHeader))) {
    $shouldCache = true;
}

$tempFilePath = $cacheFilePath . '.tmp.' . uniqid();
$lockFilePath = $cacheFilePath . '.lock';

$fpTemp = null;
$lockFp = null;

if ($shouldCache) {
    $lockFp = @fopen($lockFilePath, 'c');
    if ($lockFp && flock($lockFp, LOCK_EX | LOCK_NB)) {
        $fpTemp = @fopen($tempFilePath, 'w');
        if (!$fpTemp) {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            $lockFp = null;
            $shouldCache = false;
        }
    } else {
        if ($lockFp) {
            fclose($lockFp);
            $lockFp = null;
        }
        $shouldCache = false;
    }
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $driveUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // kita pakai WRITEFUNCTION
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$headers = [
    "Authorization: Bearer $accessToken"
];
if ($isPartialRequest && $rangeHeader) {
    $headers[] = "Range: " . $rangeHeader;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$expectedLength = null;
$headersToForward = [];
$currentHttpCode = 0;
$isHeadersSent = false;

curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$expectedLength, &$headersToForward, &$currentHttpCode, &$isHeadersSent) {
    $len = strlen($header);
    $headerClean = trim($header);
    
    // Jika kita menerima baris status HTTP baru (misal redirect 302, lalu 200)
    if (preg_match('#^HTTP/(1\.[01]|2) (\d+)#', $headerClean, $matches)) {
        $currentHttpCode = (int)$matches[2];
        if ($currentHttpCode == 200 || $currentHttpCode == 206) {
            $headersToForward = []; // Reset header untuk response final
        }
        return $len;
    }
    
    if (empty($headerClean)) {
        // Akhir dari blok header
        if (($currentHttpCode == 200 || $currentHttpCode == 206) && !$isHeadersSent) {
            http_response_code($currentHttpCode);
            foreach ($headersToForward as $h) {
                header($h);
            }
            $isHeadersSent = true;
        }
        return $len;
    }
    
    if ($currentHttpCode == 200 || $currentHttpCode == 206) {
        $lower = strtolower($headerClean);
        if (strpos($lower, 'content-type:') === 0 || 
            strpos($lower, 'content-length:') === 0 || 
            strpos($lower, 'content-range:') === 0 ||
            strpos($lower, 'accept-ranges:') === 0) {
            $headersToForward[] = $headerClean;
        }
        
        if (strpos($lower, 'content-length:') === 0) {
            $expectedLength = (int) trim(substr($headerClean, 15));
        }
    }
    
    return $len;
});

// Streaming Chunk - Langsung tulis ke output buffer dan file temporary
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) use ($fpTemp, &$shouldCache, &$currentHttpCode) {
    if ($currentHttpCode == 200 || $currentHttpCode == 206) {
        echo $data;
        flush();
        
        if ($shouldCache && $fpTemp) {
            fwrite($fpTemp, $data);
        }
    }
    return strlen($data);
});

log_message("[STREAM GDRIVE START] musicId=$musicId fileId=$fileId");

curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErrno = curl_errno($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($shouldCache && $fpTemp) {
    fclose($fpTemp);
    $actualLength = filesize($tempFilePath);
    
    if (($httpCode == 200 || $httpCode == 206) && !$curlErrno) {
        if ($expectedLength !== null && $actualLength !== $expectedLength) {
            log_message("[STREAM CACHE ERROR] Incomplete download. Expected $expectedLength, got $actualLength");
            @unlink($tempFilePath);
        } else {
            if (rename($tempFilePath, $cacheFilePath)) {
                // Insert to cache_musics
                global $db;
                if (!@mysqli_ping($db)) {
                    @$db->close();
                    $db = new mysqli(HOST, SIBEUX, pass, DB);
                    $db->set_charset('utf8mb4');
                }
                $stmt = $db->prepare("INSERT IGNORE INTO cache_musics (cache_music_id) VALUES (?)");
                if ($stmt) {
                    $stmt->bind_param("i", $musicId);
                    $stmt->execute();
                    $stmt->close();
                    log_message("[STREAM CACHE SUCCESS] musicId=$musicId fileId=$fileId");
                } else {
                    log_message("[STREAM CACHE DB ERROR] Failed to prepare statement: " . $db->error);
                }
            } else {
                log_message("[STREAM CACHE ERROR] Failed to rename temp file to $cacheFilePath");
                @unlink($tempFilePath);
            }
        }
    } else {
        log_message("[STREAM GDRIVE ERROR] HTTP Code: $httpCode, cURL Error: $curlError, musicId=$musicId");
        @unlink($tempFilePath);
    }
}

if ($lockFp) {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFilePath);
}

// Helper untuk serve local file dengan support Range
function serveLocalFileWithRange($filePath, $mimeType = 'audio/mpeg') {
    $size = filesize($filePath);
    $start = 0;
    $end = $size - 1;

    header("Content-Type: $mimeType");
    header("Accept-Ranges: bytes");

    if (isset($_SERVER['HTTP_RANGE'])) {
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        if ($range == '-') {
            $start = $size - substr($range, 1);
        } else {
            $range = explode('-', $range);
            $start = $range[0];
            $end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size - 1;
        }
        $end = ($end > $size - 1) ? $size - 1 : $end;
        if ($start > $end || $start > $size - 1 || $end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $length = $end - $start + 1;
        header('HTTP/1.1 206 Partial Content');
        header("Content-Length: $length");
        header("Content-Range: bytes $start-$end/$size");
    } else {
        $length = $size;
        header("Content-Length: $length");
    }

    $fp = @fopen($filePath, 'rb');
    if (!$fp) {
        http_response_code(500);
        exit;
    }
    
    fseek($fp, $start);
    $bufferSize = 8192;
    $bytesLeft = $length;
    while (!feof($fp) && $bytesLeft > 0 && connection_status() == 0) {
        $readSize = ($bytesLeft > $bufferSize) ? $bufferSize : $bytesLeft;
        $data = fread($fp, $readSize);
        echo $data;
        flush();
        $bytesLeft -= strlen($data);
    }
    fclose($fp);
}
