<?php
include './connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["error" => "Invalid request method."]));
}

if (!isset($_POST['file_url']) || !isset($_POST['file_name']) || !isset($_POST['file_ext'])) {
    die(json_encode(["error" => "file_url, file_name, dan file_ext harus dikirim"]));
}

$fileUrl = $_POST['file_url'];
$fileName = basename($_POST['file_name']);
$fileExt = $_POST['file_ext'];
$targetDir = 'music-host/'; // sesuaikan path
$targetFile = $targetDir . $fileName . '.' . $fileExt;
// $jsonFile = $targetDir . 'music_host_list.json';

// === Cek apakah file sudah ada di DB ===
$stmt = $db->prepare("SELECT uid_music FROM hosted_music WHERE uid_music = ?");
$stmt->bind_param("s", $fileName);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // File sudah ada, tidak perlu upload
    die(json_encode([
        "status" => "exists",
        "message" => "File sudah ada di database",
        "music_id" => $fileName
    ]));
}
$stmt->close();

// === Mulai download file ===
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

// === Insert ke database ===
$stmt = $db->prepare("INSERT INTO hosted_music (uid_music, music_dir, uploaded_at) VALUES (?, ?, NOW())");
$musicUrl = $targetFile;
$stmt->bind_param("ss", $fileName, $musicUrl);
if (!$stmt->execute()) {
    die(json_encode(["error" => "Gagal insert ke DB: " . $stmt->error]));
}
$stmt->close();

// === Tambahkan ke JSON ===
$newMusic = [
    "uid" => $fileName,
    "url" => $musicUrl,
    "uploaded_at" => date('Y-m-d H:i:s')
];

// baca file JSON yang sudah ada
// if (file_exists($jsonFile)) {
//     $jsonData = file_get_contents($jsonFile);
//     $musicList = json_decode($jsonData, true);
//     if (!is_array($musicList))
//         $musicList = [];
// } else {
//     $musicList = [];
// }

// tambahkan musik baru
// $musicList[] = $newMusic;

// simpan kembali ke JSON
// file_put_contents($jsonFile, json_encode($musicList, JSON_PRETTY_PRINT));

echo json_encode([
    "status" => "success",
    "message" => "File berhasil di-upload dan ditambahkan ke JSON",
    "music_id" => $newMusic['uid'],
    "music_url" => $newMusic['url']
]);