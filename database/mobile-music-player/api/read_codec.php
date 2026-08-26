<?php

// --- Configuration FFprobe ---
$ffprobePath = getenv('FFPROBE_PATH') ?: (isset($_ENV['FFPROBE_PATH']) ? $_ENV['FFPROBE_PATH'] : "/home/sibs6571/ffmpeg/ffprobe"); // Path FFprobe Anda

function checkCodecAudio($musicId, $filePath, $db, $ffprobePath): ?array
{
    // Jalankan FFprobe pada file local tersebut
    // Menggunakan cURL untuk memanggil API Python (Flask/FastAPI)
    // yang akan mengeksekusi ffprobe.
    // Ambil URL API dari Environment Variable, fallback ke localhost jika tidak ada
    $apiUrl = getenv('PYTHON_API_URL') ?: (isset($_ENV['PYTHON_API_URL']) ? $_ENV['PYTHON_API_URL'] : "http://127.0.0.1:5000/api/check_codec");
    $postData = json_encode([
        'file_url' => $filePath,
        'ffprobe_path' => $ffprobePath
    ]);
    
    $ch = curl_init($apiUrl);
    
    // Trik untuk membypass Cloudflare: Memaksa cURL agar langsung tembak ke IP server lokal
    // (Biar tidak muter-muter ke internet dulu yang bikin kena blokir 525 Cloudflare)
    $parsedUrl = parse_url($apiUrl);
    $apiHost = $parsedUrl['host'] ?? '';
    $apiPort = $parsedUrl['scheme'] === 'https' ? 443 : 80;
    $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
    
    curl_setopt($ch, CURLOPT_RESOLVE, ["{$apiHost}:{$apiPort}:{$serverIp}"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    // Timeout sedikit lebih besar dari script python
    curl_setopt($ch, CURLOPT_TIMEOUT, 65);
    
    $output = curl_exec($ch);
    curl_close($ch);
    
    $metadata = json_decode($output, true);

    $codecName = null;
    $musicQuality = null;
    $bitRate = null;
    $sampleRate = null;
    $bitsPerRawSample = null;

    if (json_last_error() !== JSON_ERROR_NONE || !isset($metadata['streams'][0])) {
        $logFile = __DIR__ . '/custom.log';
        $message = ["error" => "[ERROR] Gagal mendapatkan metadata valid dari ffprobe.", "ffprobe_output" => $output];
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . json_encode($message) . "\n", FILE_APPEND);
        // die(); // Menghentikan seluruh skrip php
    } else {
        $audioStream = $metadata['streams'][0];

        // 3. Ekstrak data yang dibutuhkan
        $codecName = $audioStream['codec_name'] ?? null;
        $bitRate = $metadata['format']['bit_rate'] ?? $audioStream['bit_rate'] ?? "--";
        $bitRate = $bitRate != "--" ? number_format((int) $bitRate / 1000, 0, '.', '') : "--";
        $sampleRate = $audioStream['sample_rate'] ?? '--';
        $sampleRate = $sampleRate != "--" ? (string) ((int) $sampleRate / 1000) : "--";
        $bitsPerRawSample = $audioStream['bits_per_raw_sample'] ?? "--";
        $lossyFormats = ['mp3', 'aac', 'ogg', 'vorbis', 'opus', 'wma'];
        $musicQuality = in_array(strtolower($codecName), $lossyFormats) ? "lossy" : "lossless";

        $stmt_metadata = $db->prepare(
            "INSERT INTO metadata_musics (metadata_id_music, codec_name, music_quality, sample_rate, bit_rate, bits_per_raw_sample) VALUES (?, ?, ?, ?, ?, ?)
            -- Gunakan perintah INSERT ... ON DUPLICATE KEY UPDATE. Perintah ini secara cerdas akan melakukan INSERT jika datanya baru, atau UPDATE jika datanya sudah ada. Ini sering disebut operasi \"UPSERT\" (Update or Insert)
            ON DUPLICATE KEY UPDATE
                    codec_name = VALUES(codec_name),
                    music_quality = VALUES(music_quality),
                    sample_rate = VALUES(sample_rate),
                    bit_rate = VALUES(bit_rate),
                    bits_per_raw_sample = VALUES(bits_per_raw_sample)"
        );

        // Type data: i = integer, s = string
        $stmt_metadata->bind_param(
            "isssss",
            $musicId,
            $codecName,
            $musicQuality,
            $sampleRate,
            $bitRate,
            $bitsPerRawSample
        );

        if (!$stmt_metadata->execute()) {
            // If failed, error will displayed di sini!
            die("Error inserting metadata: " . $stmt_metadata->error);
        }
        $stmt_metadata->close();
    }
    if (!empty($musicQuality) && !empty($codecName)) {
        // return nilai
        return [
            'codec_name' => $codecName,
            'music_quality' => $musicQuality,
            'sample_rate' => $sampleRate,
            'bit_rate' => $bitRate,
            'bits_per_raw_sample' => $bitsPerRawSample,
        ];
    } else {
        return null;
    }
}