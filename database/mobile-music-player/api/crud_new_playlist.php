<?php

include './connection.php';

$sql = "";

if (isset($_GET['action']) && isset($_GET['playlist_name']) && ($_GET['action'] == 'create')) {

    $name = $_GET['playlist_name'];

    $sql = "INSERT INTO playlist (uid, name, image, type, author, pin, date_pin, date, editable) 
    values (NULL, '$name', NULL, 'playlist', 'Nasrul Wahabi', 'false', NULL, NOW(), 'true')";
}

// Query to retrieve data from MySQL
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
} else {
    echo "Success";
}