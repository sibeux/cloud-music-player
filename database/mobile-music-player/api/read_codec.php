<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["error" => "Invalid request method."]));
}

// Pastikan parameter dikirim
if (!isset($_POST['url']) || empty($_POST['url'])) {
    die(json_encode(["error" => "URL tidak boleh kosong."]));
}

$file = $_POST['url'];

// Path lengkap ke ffprobe (sesuaikan dengan lokasi di hosting kamu)
$ffprobePath = "/home/sibe5579/ffmpeg/ffprobe"; // Ganti "username" dengan username cPanel-mu

// Validasi URL jika menggunakan URL, atau validasi path jika file lokal
if (!filter_var($file, FILTER_VALIDATE_URL) && !file_exists($file)) {
    die(json_encode(["error" => "File tidak valid."]));
}

// Escape shell argument untuk keamanan
// $file = escapeshellarg($file);

// Jalankan ffprobe untuk mendapatkan metadata dalam format JSON
// \"$file\" digunakan untuk menangani spasi dan karakter khusus dalam path, seperti tanda kurung
$command = "$ffprobePath -v error -show_streams -show_format -print_format json \"$file\" 2>&1";

$codec = shell_exec($command);

// Periksa apakah ffprobe memberikan output
if (empty($codec)) {
    die(json_encode(["error" => "Gagal mendapatkan metadata dari ffprobe."]));
}

// Tampilkan hasil dalam format JSON
header('Content-Type: application/json');
echo $codec;