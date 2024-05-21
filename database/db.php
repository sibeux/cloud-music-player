<?php

define('HOST', 'localhost');
define('SIBEUX', 'sibk1922_cbux	');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibk1922_cloud_music');

// define('HOST', 'localhost');
// define('SIBEUX', 'root');
// define('pass', '');
// define('DB', 'sibk1922_cloud_music');

$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

$db->set_charset('utf8mb4');