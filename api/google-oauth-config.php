<?php

// Ambil data API sekali saja saat file ini di-include
$url = "https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/gdrive_api";
$goauthResponse = @file_get_contents($url);
$allApiData = ($goauthResponse) ? json_decode($goauthResponse, true) : [];

/**
 * Fungsi ini sekarang MENGEMBALIKAN kredensial, bukan mengubah variabel luar.
 * Ia juga menerima data API sebagai parameter agar tidak bergantung pada variabel global.
 *
 * @param string $email Email target.
 * @param array $apiData Seluruh data dari API.
 * @return array|null
 */
function getGoogleDriveCredentials(string $email, array $apiData): ?array
{
    $clientId = null;
    $clientSecret = null;
    $refreshToken = null;

    $emailPrefix = explode('@', $email)[0];
    if (empty($emailPrefix)) {
        var_dump("No authorization code received.");
        return null;
    }

    // Lakukan perulangan pada data yang diberikan
    foreach ($apiData as $item) {
        if (!isset($item['email'])) continue;

        if ($item['email'] === 'wahabinasrul_googleoauth_client_id') {
            $clientId = $item['gdrive_api'];
        } else if ($item['email'] === 'wahabinasrul_googleoauth_client_secret') {
            $clientSecret = $item['gdrive_api'];
        } else if ($item['email'] === $emailPrefix . '_googleoauth_refresh_token') {
            $refreshToken = $item['gdrive_api'];
        }
    }

    // Jika salah satu null, mungkin data tidak lengkap
    // KHUSUS KONDISI INI JIKA DIGUNAKAN UNTUK GET REFRESH TOKEN,-
    // MAKA BUAT DATA DUMMY REFRESH TOKEN DULU DI SQL, AGAR KONDISI INI TIDAK TRUE.
    if ($clientId === null || $clientSecret === null || $refreshToken === null) {
        return null;
    }

    // Kembalikan hasilnya dalam bentuk array
    return [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'refresh_token' => $refreshToken,
    ];
}

// Tidak ada lagi 'return' di akhir file ini.
// Tugas file ini sekarang adalah menyediakan variabel $allApiData
// dan fungsi getGoogleDriveCredentials.