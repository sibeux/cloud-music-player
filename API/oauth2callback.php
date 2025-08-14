<?php
// config.php harus ada Client ID & Client Secret + redirect_uri
$config = include './google-oauth-config.php'; 

// Ambil code dari query parameter
$code = $_GET['code'] ?? null;

if (!$code) {
    die("No authorization code received.");
}

// Tukar code menjadi access token & refresh token
$tokenUrl = "https://oauth2.googleapis.com/token";

$data = http_build_query([
    'code' => $code,
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'redirect_uri' => 'https://sibeux.my.id/cloud-music-player/api/oauth2callback.php', // harus sama dengan yang didaftarkan
    'grant_type' => 'authorization_code',
]);

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    die("Curl error: " . curl_error($ch));
}
curl_close($ch);

// Parse hasil JSON
$tokenData = json_decode($response, true);

if (isset($tokenData['refresh_token'])) {
    echo "Access Token: " . $tokenData['access_token'] . "<br>";
    echo "Refresh Token: " . $tokenData['refresh_token'] . "<br>";

    // Opsional: simpan ke file
    file_put_contents('token.json', json_encode($tokenData, JSON_PRETTY_PRINT));
} else {
    echo "Failed to get tokens. Response: <pre>" . print_r($tokenData, true) . "</pre>";
}