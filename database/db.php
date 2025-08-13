<?php

// Yang perlu diganti saat hosting baru: username, password, dan db.

define('HOST', 'localhost');
define('SIBEUX', 'sibs6571_cbux'); // Ganti dengan username database hosting
define('pass', '1NvgEHFnwvDN96'); // Ganti dengan password database hosting
define('DB', 'sibs6571_cloud_music'); // Ganti dengan nama database hosting

// define('HOST', 'localhost');
// define('SIBEUX', 'root');
// define('pass', '');
// define('DB', 'sibk1922_cloud_music');

$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

$db->set_charset('utf8mb4');