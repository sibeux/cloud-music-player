<?php

// Load Composer Autoload (Sesuaikan path jika file ini ada di dalam subfolder)
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/mobile-music-player/api/connection.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';

use Dotenv\Dotenv;

// Load file .env
// File .env taruh di tempat yang sama dengan composer.json/index.php (root)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // Pakai safeLoad agar tidak error fatal jika file .env lupa dibuat

// Ambil Secret Key
$secretKey = $_ENV['CF_WORKER_SECRET'] ?? null;

if (!$secretKey) {
    die("Error: Secret key belum disetting di .env");
}

function getSecureCdnUrl($filePath, $secretKey, $expirySeconds = 3600)
{
    // 1. Bersihkan path (pastikan diawali /)
    $path = '/' . ltrim($filePath, '/');

    // 2. Tentukan waktu expired (Unix timestamp)
    $expires = time() + $expirySeconds;

    // 3. Buat string yang akan ditandatangani: "path + expires"
    $stringToSign = $path . $expires;

    // 4. Buat hash SHA256
    $signature = hash_hmac('sha256', $stringToSign, $secretKey);

    // 5. Kembalikan URL lengkap
    // Ganti dengan domain worker kamu
    $cdnDomain = 'https://cdn.sibeux.my.id';

    return "{$cdnDomain}{$path}?verify={$signature}&expires={$expires}";
}

function streamMusicFromCF($secretKey, $db, $ffprobePath, $file, $musicId)
{
    // --- CONTOH PENGGUNAAN ---
    // $file = "/albums/nirvana/smellsliketeenspirit.mp3";
    // $file = $_GET['path'] ?? null;
    // $musicId = $_GET['music_id'] ?? null;

    // Dokumentasi url handled: https://chatgpt.com/c/692a7a4a-f358-8323-8d44-d770ab9f9b63
    $dir = dirname($file);
    $base = basename($file);       // hanya "Elven Dreams.m4a"
    $encodedBase = rawurlencode($base);

    // contoh: /music/flac/Elven%20Dreams.m4a
    $finalPath = $dir . '/' . $encodedBase;

    // echo getSecureCdnUrl($file, $secret);
    // Output: https://cdn.../file.mp3?verify=a1b2c3...&expires=17000000
    $rawUrl = getSecureCdnUrl($finalPath, $secretKey);
    $streamUrl = $rawUrl;

    // KIRIM RESPON MANUAL (Tiru isi sendJsonResponses tapi tanpa die) ---
    http_response_code(200);
    header('Content-Type: application/json');
    // Header anti-cache (Sesuai fungsi helper)
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    outputJson([
        "success" => true,
        "music_id" => $musicId,
        "stream_url" => $streamUrl
    ]);

    finishResponse();

    // Tetap berjalan setelah client menerima response
    checkCodecAudio($musicId, $streamUrl, $db, $ffprobePath);

    exit();
}