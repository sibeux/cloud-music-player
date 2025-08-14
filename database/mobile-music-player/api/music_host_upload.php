<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["error" => "Invalid request method."]));
}

if (!isset($_POST['file_url']) || !isset($_POST['file_name'])) {
    die(json_encode(["error" => "file_url dan file_name harus dikirim"]));
}

$fileUrl = $_POST['file_url'];
$fileName = basename($_POST['file_name']);
$targetDir = 'music-host/'; // sesuaikan path
$targetFile = $targetDir . $fileName;
$jsonFile = $targetDir . 'music_host_list.json';

// buka file tujuan untuk ditulis
$fp = fopen($targetFile, 'w+');
if ($fp === false) {
    die(json_encode(["error" => "Gagal membuka file tujuan"]));
}

// inisialisasi cURL
$ch = curl_init($fileUrl);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

// eksekusi cURL
if (!curl_exec($ch)) {
    fclose($fp);
    unlink($targetFile);
    die(json_encode(["error" => "Download gagal: " . curl_error($ch)]));
}

curl_close($ch);
fclose($fp);

// === Tambahkan ke JSON ===
$newMusic = [
    "name" => $fileName,
    "url" => $targetDir . $fileName,
    "uploaded_at" => date('Y-m-d H:i:s')
];

// baca file JSON yang sudah ada
if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $musicList = json_decode($jsonData, true);
    if (!is_array($musicList))
        $musicList = [];
} else {
    $musicList = [];
}

// tambahkan musik baru
$musicList[] = $newMusic;

// simpan kembali ke JSON
file_put_contents($jsonFile, json_encode($musicList, JSON_PRETTY_PRINT));

echo json_encode([
    "status" => "success",
    "message" => "File berhasil di-upload dan ditambahkan ke JSON",
    "music_id" => $newMusic['name'],
    "music_url" => $newMusic['url']
]);