<?php

include './connection.php';

$sql = "";
$metadata_sql = "";
$delete = "";

if (isset($_POST['music_id'])) {

    $_id = $_POST['music_id'];
    $codec_name = $_POST['codec_name'];
    $quality = $_POST['quality'];
    $bits_per_raw_sample = $_POST['bits_per_raw_sample'];
    $sample_rate = $_POST['sample_rate'];
    $bit_rate = $_POST['bit_rate'];

    // kalau mau tidak ada duplikasi musik di recents, maka uid_music harus dijadikan unique/foreign key
    $sql = "INSERT INTO recents_music (uid_recents, uid_music, played_at) values (NULL, '$_id', NOW())";
    $metadata_sql = "INSERT INTO `metadata_music` (`metadata_id_music`, `codec_name`, `music_quality`, `sample_rate`, `bit_rate`, `bits_per_raw_sample`) VALUES ($_id, $codec_name, $quality, $bits_per_raw_sample, $sample_rate, $bit_rate);"
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
$result = $db->query($metadata_sql);
$result = $db->query($delete);

// Check if the query was successful
if (!$result) {
    foreach ($db->errorInfo() as $error) {
        die("Query failed: " . $error);
    }
} else {
    echo "Success";
}