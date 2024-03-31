<?php

define('HOST', 'localhost');
define('SIBEUX', 'sibk1922');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibk1922_cloud_music');

$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

$query = $db->query("SELECT * FROM music ORDER BY title ASC");
$result = array();

$rows = mysqli_fetch_all($query, MYSQLI_ASSOC);

echo json_encode($rows);