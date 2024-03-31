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

while ($row = mysqli_fetch_array($query)) {
    $result[] = $row;
}

echo json_encode($result);
