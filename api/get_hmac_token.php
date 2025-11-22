<?php

// 1. Load Composer Autoload (Sesuaikan path jika file ini ada di dalam subfolder)
require __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/../database/mobile-music-player/api/connection.php';
require_once __DIR__ . '/../database/mobile-music-player/api/read_codec.php';

use Dotenv\Dotenv;

// 2. Load file .env
// File .env taruh di tempat yang sama dengan composer.json/index.php (root)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // Pakai safeLoad agar tidak error fatal jika file .env lupa dibuat

// 3. Ambil Secret Key
$secretKey = $_ENV['CF_WORKER_SECRET'] ?? null;

if (!$secretKey) {
    die("Error: Secret key belum disetting di .env");
}

function getSecureCdnUrl($filePath, $secretKey, $expirySeconds = 3600) {
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

// --- CONTOH PENGGUNAAN ---
// $file = "/albums/nirvana/smellsliketeenspirit.mp3";
$file = $_GET['path'] ?? null;
$musicId = $_GET['music_id'] ?? null;

// echo getSecureCdnUrl($file, $secret);
// Output: https://cdn.../file.mp3?verify=a1b2c3...&expires=17000000
$streamUrl = getSecureCdnUrl($file, $secretKey);
// BUAT PAYLOAD
$responsePayload = [
        "success" => true,
        "music_id" => $musicId,
        "stream_url" => $streamUrl,
];

// KIRIM RESPON MANUAL (Tiru isi sendJsonResponses tapi tanpa die) ---
http_response_code(200);
header('Content-Type: application/json');
// Header anti-cache (Sesuai fungsi helper)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

echo json_encode($responsePayload);

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
// Fungsi berat ini sekarang aman dijalankan tanpa bikin user lemot
checkCodecAudio($musicId, $cacheFilePath, $db, $ffprobePath);

exit();
?>