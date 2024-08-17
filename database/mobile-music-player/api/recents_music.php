<?php

include './connection.php';

$sql = "";
$delete = "";

if (isset($_GET['_id'])) {

    $_id = $_GET['_id'];

    $sql = "INSERT INTO recents_music (uid_recents, uid_music, plated_at) values (NULL, '$_id', NOW())";
    $delete = "DELETE FROM recents_music
WHERE uid_recents NOT IN (
    SELECT uid_recents
    FROM (
        SELECT uid_recents
        FROM recents_music
        ORDER BY played_at DESC
        LIMIT 100
    ) AS last_100
);";
}

// Query to retrieve data from MySQL
$result = $conn->query($sql);
$result = $conn->query($delete);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
} else {
    echo "Success";
}