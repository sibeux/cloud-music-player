<?php

// =========================================================
// MENCEGAH SCRIPT BERHENTI KETIKA MAIN THREAD SELESAI
// =========================================================
ignore_user_abort(true);
set_time_limit(0);

// WORKAROUND UNTUK LITESPEED / CPANEL TANPA FASTCGI_FINISH_REQUEST:
// Kita paksa server mengirim header Connection: close lalu flush,
// agar koneksi cURL dari stream_drive terputus dengan sukses,
// sementara script ini tetap berjalan di background!
if (php_sapi_name() !== 'cli') {
    header("Connection: close");
    header("Content-Length: 0");
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    if (session_id()) session_write_close();
}

// PENTING: Samakan working directory dengan stream_drive.php 
// agar file 'custom.log' ditulis ke folder yang SAMA persis.
chdir(__DIR__ . '/music/stream');

// Gunakan sementara fallback log dasar kalau utils.php belum ter-load
$tempLog = __DIR__ . '/cache_worker_debug.log';
file_put_contents($tempLog, "[" . date('Y-m-d H:i:s') . "] WORKER HIT! POST Data: " . json_encode($_POST) . "\n", FILE_APPEND);

require_once __DIR__ . '/../utils/utils.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';
require_once __DIR__ . '/google-oauth-config.php';
require_once __DIR__ . '/music/stream/get_gdrive_oauth_token.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// =========================================================
// AMBIL PARAMETER (BISA DARI CURL POST ATAU CLI ARGUMENTS)
// =========================================================
$musicId = $_POST['musicId'] ?? ($argv[1] ?? null);
$fileId = $_POST['fileId'] ?? ($argv[2] ?? null);
$fileType = $_POST['fileType'] ?? ($argv[3] ?? null);
$ffprobePath = $_POST['ffprobePath'] ?? ($argv[4] ?? null);

log_message("[CACHE WORKER INCOMING] POST Data: " . json_encode($_POST));

if (!$musicId || !$fileId) {
    log_message("[ERROR] cacheMusicWorker started without musicId or fileId");
    exit;
}

log_message("[CACHE WORKER INIT] Starting worker for musicId=$musicId fileId=$fileId");

// Pastikan variabel $db tersedia dari db.php
global $db;
if (!$db) {
    log_message("[ERROR] Database connection failed in cacheMusicWorker");
    exit;
}

// =========================================================
// 1. AMBIL INFORMASI UPLOADER DAN SUSPICIOUS FLAG
// =========================================================
$music = [];
if ($fileType !== "image") {
    $stmt = $db->prepare("SELECT uploader, is_suspicious FROM musics WHERE id_music = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $musicId);
        $stmt->execute();
        $result = $stmt->get_result();
        $music = $result->fetch_assoc() ?: [];
        $stmt->close();
    }
}

$uploader = $music['uploader'] ?? null;
$isSuspicious = filter_var($music['is_suspicious'] ?? false, FILTER_VALIDATE_BOOLEAN);

if (!$isSuspicious) {
    $uploader = "wahabinasrul@gmail.com";
}

// =========================================================
// 2. DAPATKAN ACCESS TOKEN OAUTH
// =========================================================
// Variabel $allApiData otomatis tersedia karena file google-oauth-config.php di-require di atas
$config = getGoogleDriveCredentials($uploader, $allApiData);
$tokenData = getGdriveOauthToken($config, $isSuspicious);

if (empty($tokenData['access_token'])) {
    log_message("[ERROR] cacheMusicWorker failed to get Google Drive access token for musicId=$musicId");
    exit;
}

$accessToken = $tokenData['access_token'];

$driveUrl = "https://www.googleapis.com/drive/v3/files/" . rawurlencode($fileId) . "?alt=media";
if ($isSuspicious) {
    $driveUrl .= "&acknowledgeAbuse=true";
}

// =========================================================
// 3. CACHE DIRECTORY & LOCKING
// =========================================================
$cacheDir = __DIR__ . '/../database/mobile-music-player/api/music-host';
$cacheDuration = 31536000;
$safeFileId = basename($fileId);
$cacheFilePath = $cacheDir . '/' . $safeFileId;

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$lockFilePath = $cacheFilePath . '.lock';
$lockFp = fopen($lockFilePath, 'c');

if (!$lockFp) {
    log_message("[ERROR] cacheMusicWorker cannot create lock file: " . $lockFilePath);
    exit;
}

$lockAcquired = flock($lockFp, LOCK_EX | LOCK_NB);

if (!$lockAcquired) {
    log_message("[CACHE WORKER ABORT] Another process is already caching musicId=$musicId");
    fclose($lockFp);
    exit;
}

// =========================================================
// 4. DOUBLE CHECK JIKA FILE CACHE SUDAH DIBUAT
// =========================================================
$isCacheFileValid = file_exists($cacheFilePath) && is_file($cacheFilePath) && filesize($cacheFilePath) > 0 && (time() - filemtime($cacheFilePath) < $cacheDuration);

if ($isCacheFileValid) {
    log_message("[CACHE WORKER ABORT] Cache already exists for musicId=$musicId");
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

// =========================================================
// 5. DOWNLOAD GOOGLE DRIVE -> TEMP FILE
// =========================================================
$tempFilePath = $cacheFilePath . '.tmp.' . getmypid();
log_message("[BG] Creating temp file: " . $tempFilePath);

$tempFp = fopen($tempFilePath, 'wb');
if (!$tempFp) {
    log_message("[ERROR] Cannot create temp file: " . $tempFilePath);
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

log_message("[CACHE DOWNLOAD IN PROGRESS] Starting Google Drive download for musicId=$musicId");

$ch = curl_init($driveUrl);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $accessToken],
    CURLOPT_FOLLOWLOCATION => false, // KITA HANDLE REDIRECT MANUAL!
    CURLOPT_HEADER => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY => true, // Cukup header saja untuk cek redirect
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$headerResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

log_message("[BG DEBUG] GDrive API initial HTTP=$httpCode Redirect=" . ($redirectUrl ?: "NONE"));

$finalDownloadUrl = $driveUrl;

// Jika Google Drive membalas dengan 301, 302, 303, 307, 308 (Redirect)
if ($httpCode >= 300 && $httpCode < 400 && !empty($redirectUrl)) {
    $finalDownloadUrl = $redirectUrl;
} else if ($httpCode >= 400) {
    // Jika API langsung mengembalikan error (sebelum redirect)
    $errorBody = substr($headerResponse, 0, 500);
    log_message("[CACHE DOWNLOAD FAILED] Google Drive API returned HTTP=$httpCode before redirect. Body: $errorBody");
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

// SEKARANG MULAI DOWNLOAD SESUNGGUHNYA
log_message("[BG DEBUG] Starting final download from: " . explode('?', $finalDownloadUrl)[0] . "...");

$chDown = curl_init($finalDownloadUrl);
curl_setopt_array($chDown, [
    // PENTING: Jangan kirim header Authorization jika diarahkan ke googleusercontent.com
    // karena URL redirect sudah mengandung token otorisasi di query string-nya!
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => false,
    CURLOPT_FILE => $tempFp,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 0, // Tidak dibatasi
    CURLOPT_FAILONERROR => false,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

// Jika TIDAK redirect, berarti kita masih di URL awal dan butuh header Authorization
if ($finalDownloadUrl === $driveUrl) {
    curl_setopt($chDown, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $accessToken]);
}

$result = curl_exec($chDown);
$httpCode = curl_getinfo($chDown, CURLINFO_HTTP_CODE);
$curlError = curl_error($chDown);
$curlErrno = curl_errno($chDown);

curl_close($chDown);
fclose($tempFp);

log_message("[BG DEBUG] CURL FINISHED for musicId=$musicId HTTP=$httpCode");

// =========================================================
// 6. VALIDASI DOWNLOAD
// =========================================================
if ($result === false || $httpCode < 200 || $httpCode >= 300) {
    $errorBody = "";
    if (file_exists($tempFilePath) && filesize($tempFilePath) > 0) {
        $errorBody = file_get_contents($tempFilePath);
        // Truncate jika terlalu panjang
        if (strlen($errorBody) > 500) $errorBody = substr($errorBody, 0, 500) . '...';
    }
    log_message("[CACHE DOWNLOAD FAILED] HTTP=$httpCode errno=$curlErrno error=$curlError | Body: $errorBody");
    
    if (file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

clearstatcache(true, $tempFilePath);
$tempFileSize = file_exists($tempFilePath) ? filesize($tempFilePath) : 0;

if ($tempFileSize <= 0) {
    log_message("[CACHE DOWNLOAD FAILED] Temp file is empty.");
    if (file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

// =========================================================
// 7. ATOMIC RENAME (TEMP -> FINAL CACHE)
// =========================================================
log_message("[BG] Attempting atomic rename: $tempFilePath -> $cacheFilePath");

if (!rename($tempFilePath, $cacheFilePath)) {
    log_message("[ERROR] Failed to rename temp cache file.");
    if (file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

clearstatcache(true, $cacheFilePath);
if (!file_exists($cacheFilePath) || filesize($cacheFilePath) <= 0) {
    log_message("[ERROR] Cache file does not exist after rename.");
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    exit;
}

// =========================================================
// 8. INSERT DB CACHE & CODEC CHECK
// =========================================================
sendToSqlCache($db, $fileId, $musicId);

if ($fileType === "audio") {
    log_message("[BG] Starting codec check. musicId=$musicId");
    checkCodecAudio($musicId, $cacheFilePath, $db, $ffprobePath);
    log_message("[BG] Codec check finished. musicId=$musicId");
}

log_message("[CACHE DOWNLOAD SUCCESS] musicId=$musicId fileId=$fileId size=" . filesize($cacheFilePath) . " bytes");

// =========================================================
// SELESAI
// =========================================================
flock($lockFp, LOCK_UN);
fclose($lockFp);
exit;

// =============================================================
// HELPER (Dipindahkan dari streamingMusicFromGdrive)
// =============================================================
function sendToSqlCache($db, $fileId, $musicId) {
    $stmt = $db->prepare("
        INSERT INTO cache_musics (cache_music_id)
        SELECT ? WHERE NOT EXISTS (
            SELECT 1 FROM cache_musics WHERE cache_music_id = ?
        )
    ");

    if (!$stmt) {
        log_message("[ERROR] Failed to prepare cache DB query: " . $db->error);
        return;
    }

    $stmt->bind_param("ii", $musicId, $musicId);

    if (!$stmt->execute()) {
        log_message("[ERROR] Failed inserting cache_musics: " . $stmt->error);
    } else {
        log_message("[CACHE DB UPDATED] fileId=$fileId musicId=$musicId");
    }

    $stmt->close();
}
