<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/music/stream/get_gdrive_oauth_token.php';
require_once __DIR__ . '/google-oauth-config.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$musicId = 18762;
$stmt = $db->prepare("SELECT m.link_gdrive, m.uploader, m.is_suspicious FROM musics m WHERE m.id_music = ? LIMIT 1");
$stmt->bind_param("i", $musicId);
$stmt->execute();
$music = $stmt->get_result()->fetch_assoc();
$stmt->close();

$regexFileIdGdrive = '/\/d\/([a-zA-Z0-9_-]+)|files\/([a-zA-Z0-9_-]+)/';
preg_match($regexFileIdGdrive, $music['link_gdrive'], $matches);
$fileId = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : null);

$uploader = $music['uploader'] ?? null;
$isSuspicious = filter_var($music['is_suspicious'] ?? false, FILTER_VALIDATE_BOOLEAN);
if (!$isSuspicious) {
    $uploader = "wahabinasrul@gmail.com";
}

$config = getGoogleDriveCredentials($uploader, $allApiData);
$tokenData = getGdriveOauthToken($config, $isSuspicious);
$accessToken = $tokenData['access_token'];

$driveUrl = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
if ($isSuspicious) {
    $driveUrl .= "&acknowledgeAbuse=true";
}

echo "Testing URL: $driveUrl\n";

$ch = curl_init($driveUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP CODE: $httpCode\n";
echo "Response:\n";
echo substr($response, 0, 1000); // print headers and start of body
