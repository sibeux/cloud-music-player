<?php
// Sertakan file dan fungsi yang diperlukan
include "./database/db.php";
// Sertakan juga file yang berisi fungsi getApiKey(), checkUrlFromDrive(), dan render_music_list()
include "index.php"; // Cara mudah untuk menyertakan semua fungsi di atas

// Validasi input halaman
$page = isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT) ? (int)$_GET['page'] : 1;

// Dapatkan API key
$api_key = getApiKey();

if ($api_key) {
    // Panggil fungsi yang sama untuk merender daftar musik
    render_music_list($db, $api_key, $page);
}
// Tidak ada output lain selain HTML dari `render_music_list`
?>