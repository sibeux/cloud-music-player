<?php

include './connection.php';

$sql = "";
$date = "";

if (isset($_GET['_favorite']) && isset($_GET['_id'])) {

    $_favorite = $_GET['_favorite'];
    $_id = $_GET['_id'];

    $sql = "UPDATE music SET favorite = '$_favorite' WHERE id_music = '$_id'";
    $date = "UPDATE playlist SET date = NOW() WHERE type = 'favorite'";
}

// Query to retrieve data from MySQL
$result = $db->query($sql);
$result = $db->query($date);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $db->error);
} else {
    echo "Success";
}