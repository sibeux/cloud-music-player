<?php

define('HOST', 'localhost');
define('SIBEUX', 'sibk1922_cbux');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibk1922_cloud_music');

$conn = new mysqli(HOST, SIBEUX, pass, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');