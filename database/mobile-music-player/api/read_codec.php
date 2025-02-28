<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Pastikan parameter dikirim
if (!isset($_POST['url']) || empty($_POST['url'])) {
    die("URL tidak boleh kosong.");
}

$file = $_POST['url'];

// Validasi URL jika menggunakan URL, atau validasi path jika file lokal
if (!filter_var($file, FILTER_VALIDATE_URL) && !file_exists($file)) {
    die("File tidak valid.");
}

// Escape shell argument untuk mencegah command injection
$file = escapeshellarg($file);

// Jalankan ffprobe untuk mendapatkan codec info dalam format JSON
$command = "ffprobe -i $file -show_streams -show_format -print_format json 2>&1";
$codec = shell_exec($command);

// Periksa apakah ffprobe memberikan output
if ($codec === null) {
    die("Gagal mendapatkan metadata.");
}

// Tampilkan hasil dalam format yang lebih rapi
header('Content-Type: application/json');
echo $codec;