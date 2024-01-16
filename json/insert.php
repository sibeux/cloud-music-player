<?php
include "../database/db.php";

// File json yang akan dibaca
$file = "music.json";

// Mendapatkan file json
$anggota = file_get_contents($file);

// Mendecode anggota.json
$data = json_decode($anggota, true);

// Data array baru
$data[] = array(
    'id_music'     => 1,
    'link' => 'https://www.youtube.com/watch?v=5qap5aO4i9A'
);

// Mengencode data menjadi json
$jsonfile = json_encode($data, JSON_PRETTY_PRINT);

// Menyimpan data ke dalam anggota.json
$anggota = file_put_contents($file, $jsonfile);