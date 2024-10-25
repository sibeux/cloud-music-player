<?php

include './connection.php';

$sql = "";
$sql2 = "";

if (isset($_GET['action']) && isset($_GET['playlist_name']) && ($_GET['action'] == 'create')) {

    $name = $_GET['playlist_name'];

    $sql = "INSERT INTO playlist (uid, name, image, type, author, pin, date_pin, date, editable) 
    values (NULL, '$name', NULL, 'playlist', 'Nasrul Wahabi', 'false', NULL, NOW(), 'true')";
}

if (isset($_GET['action']) && isset($_GET['playlist_uid']) && ($_GET['action'] == 'delete')) {
    $uid = $_GET['playlist_uid'];

    $sql = "DELETE FROM playlist WHERE playlist.uid = '$uid'";
    $sql2 = "DELETE FROM playlist_music WHERE playlist_music.id_playlist = '$uid'";
}

// Query to retrieve data from MySQL
$result = $db->query($sql);
$result = $db->query($sql2);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $db->error);
} else {
    echo "Success";
}