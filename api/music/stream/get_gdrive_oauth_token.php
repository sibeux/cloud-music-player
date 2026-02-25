<?php

function getGdriveOauthToken($config, $isSuspicious)
{
    $tokenFile = __DIR__ . '/../../token.json';

    // Coba ambil dari session (cache paling cepat)
    if (isset($_SESSION['gdrive_token']) && time() < $_SESSION['gdrive_token']['expires_at']) {
        return $_SESSION['gdrive_token'];
    }

    // Jika tidak ada di session atau sudah expired, baca dari file
    if (!file_exists($tokenFile)) {
        http_response_code(500);
        log_message("[ERROR] Token file not found. Please run authentication flow first.");
        die("Token file not found. Please run authentication flow first.");
    }

    $fp = fopen($tokenFile, 'r+');
    if (!flock($fp, LOCK_EX)) { // Kunci file secara eksklusif untuk mencegah proses lain mengganggu
        http_response_code(503);
        log_message("[ERROR] Could not get file lock. Server is busy.");
        die("Could not get file lock. Server is busy.");
    }

    $tokenData = json_decode(fread($fp, filesize($tokenFile)), true);

    // Refresh token jika sudah expired atau file ditandai suspicous
    if (time() >= $tokenData['expires_at'] || $isSuspicious == 'true') {
        log_message("File terindikasi suspicous / refresh token sudah expired. Mencoba mendapatkan refresh token yang baru...");

        // Lakukan pengecekan jika config tidak ditemukan
        if (!$config) {
            log_message("Konfigurasi tidak ditemukan atau tidak lengkap.");
            die("Konfigurasi tidak ditemukan atau tidak lengkap.");
        }

        $postData = http_build_query([
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $config['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $resp = curl_exec($ch);
        $respData = json_decode($resp, true);
        if (!isset($respData['access_token'])) {
            flock($fp, LOCK_UN); // Lepas kunci sebelum mati
            fclose($fp);
            http_response_code(500);
            log_message($config['client_id'] . $config['client_secret'] . $config['refresh_token']);
            log_message("[ERROR] Failed to refresh access token: " . $resp);
            die("Failed to refresh access token: " . $resp);
        }

        $tokenData['access_token'] = $respData['access_token'];
        $tokenData['expires_at'] = time() + $respData['expires_in'] - 60; // Kurangi 60 detik sebagai buffer

        log_message("[SUCCESS] Token has refreshed");

        // Update file token.json dengan token baru
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($tokenData, JSON_PRETTY_PRINT));
    }

    flock($fp, LOCK_UN); // Lepas kunci
    fclose($fp);

    // Simpan di session untuk request berikutnya ---
    $_SESSION['gdrive_token'] = $tokenData;
    return $tokenData;
}