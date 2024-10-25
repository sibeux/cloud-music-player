<?php

include './connection.php';

$sql = "";
$delete = "";

if (isset($_GET['_id'])) {

    $_id = $_GET['_id'];

    // kalau mau tidak ada duplikasi musik di recents, maka uid_music harus dijadikan unique/foreign key
    $sql = "INSERT INTO recents_music (uid_recents, uid_music, played_at) values (NULL, '$_id', NOW())";
    $delete = "DELETE FROM recents_music
WHERE uid_recents NOT IN (
    SELECT uid_recents
    FROM (
        SELECT uid_recents
        FROM recents_music
        ORDER BY played_at DESC
        LIMIT 500
    ) AS last_500
);";
}

// Query to retrieve data from MySQL
$result = $db->query($sql);
$result = $db->query($delete);

// Check if the query was successful
if (!$result) {
    foreach ($db->errorInfo() as $error) {
        die("Query failed: " . $error);
    }
} else {
    echo "Success";
}