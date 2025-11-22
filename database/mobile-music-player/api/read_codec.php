<?php

// --- Configuration FFprobe ---
$ffprobePath = "/home/sibs6571/ffmpeg/ffprobe"; // Path FFprobe Anda

// --- Fungsi Helper ---
function sendJsonResponse(array $data, int $responseCode = 200)
{
    http_response_code($responseCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    die();
}

function checkCodecAudio($musicId, $filePath, $db, $ffprobePath): ?array
{
    // Jalankan FFprobe pada file local tersebut
    // WAJIB: Amankan path file untuk mencegah command injection
    $safeFilePath = escapeshellarg($filePath);
    // Bangun perintah yang akan dieksekusi
    $command = "$ffprobePath -v error -show_streams -show_format -print_format json $safeFilePath 2>&1";
    // Jalankan perintah (ini butuh izin dari hosting)
    $output = shell_exec($command);
    $metadata = json_decode($output, true);

    $codecName = null;
    $musicQuality = null;
    $bitRate = null;
    $sampleRate = null;
    $bitsPerRawSample = null;

    if (json_last_error() !== JSON_ERROR_NONE || !isset($metadata['streams'][0])) {
        $logFile = 'custom.log';
        $message = ["error" => "[ERROR] Gagal mendapatkan metadata valid dari ffprobe.", "ffprobe_output" => $output];
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . json_encode($message) . "\n", FILE_APPEND);
        // die(); // Menghentikan seluruh skrip php
    } else {
        $audioStream = $metadata['streams'][0];

        $logFile = 'custom.log';
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . json_encode($bitRate, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

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
            "INSERT INTO metadata_music (metadata_id_music, codec_name, music_quality, sample_rate, bit_rate, bits_per_raw_sample) VALUES (?, ?, ?, ?, ?, ?) 
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