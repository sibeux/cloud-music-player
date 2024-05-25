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

$sql = "";

if (isset($_GET['_favorite']) && isset($_GET['_id'])) {

    $_favorite = $_GET['_favorite'];
    $_id = $_GET['_id'];

    $sql = "UPDATE music SET favorite = '$_favorite' WHERE id_music = '$_id'";
}

// Query to retrieve data from MySQL
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
} else {
    echo "Success";
}