<?php
// File json yang akan dibaca (full path file)
$file = "./music.json";

// Mendapatkan file json
$anggota = file_get_contents($file);

// Mendecode anggota.json
$data = json_decode($anggota, true);

// Membaca data array menggunakan foreach
foreach ($data as $d) {
    echo $d['id_music'] . "<br>";
    echo $d['link'] . "<br>";
}