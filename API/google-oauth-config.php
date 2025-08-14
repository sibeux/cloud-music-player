<?php

// Ambil API key dari endpoint kamu
$url = "https://sibeux.my.id/cloud-music-player/database/mobile-music-player/api/gdrive_api";
$goauthResponse = file_get_contents($url);

if ($goauthResponse === FALSE) {
    die(json_encode(['status' => 'error', 'message' => 'Error accessing API.']));
}

$data = json_decode($goauthResponse, true);
$clientId = null; // Initialize accountKey as null
$clientSecret = null; // Initialize accountKey as null

foreach ($data as $item) {
    if (isset($item['email']) && $item['email'] === 'sibesibe86_googleoauth_client_id') {
        $clientId = $item['gdrive_api'];
    } else if (isset($item['email']) && $item['email'] === 'sibesibe86_googleoauth_client_secret') {
        $clientSecret = $item['gdrive_api'];
    }
}

// Penggunaan return
// Cara return [...] di PHP hanya bisa dipakai kalau file ini di-include.
// Kalau dipakai sebagai file standalone → tidak akan berfungsi seperti yang kamu harapkan.
return [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
];