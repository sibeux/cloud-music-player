<?php
// File json yang akan dibaca
$file = "./music.json";

// Mendapatkan file json
$anggota = file_get_contents($file);

// Mendecode anggota.json
$data = json_decode($anggota, true);

// Membaca data array menggunakan foreach
$index = 1;
foreach ($data as $key => $d) {
    // Hapus data kedua
    if ($d['id_music'] === $index) {
        // Menghapus data array sesuai dengan index
        // Menggantinya dengan elemen baru
        array_splice($data, $key, $index);
    }
    $index++;
}

// Mengencode data menjadi json
$jsonfile = json_encode($data, JSON_PRETTY_PRINT);

// Menyimpan data ke dalam anggota.json
$anggota = file_put_contents($file, $jsonfile);