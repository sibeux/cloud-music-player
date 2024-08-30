<?php

include './connection.php';

$sql = "";

function getFourCovercategory()
{
    global $sql;

    $sql = "SELECT p.uid AS playlist_uid,
    COALESCE(MAX(CASE WHEN rc.rank = 1 THEN rc.cover END), null) AS cover_1,
    COALESCE(MAX(CASE WHEN rc.rank = 2 THEN rc.cover END), null) AS cover_2,
    COALESCE(MAX(CASE WHEN rc.rank = 3 THEN rc.cover END), null) AS cover_3,
    COALESCE(MAX(CASE WHEN rc.rank = 4 THEN rc.cover END), null) AS cover_4,
    COUNT(DISTINCT rc.cover) AS total_non_null_cover
FROM playlist p
LEFT JOIN (
    SELECT min(title) as title, cover, category,
        ROW_NUMBER() OVER (PARTITION BY category ORDER BY title ASC) AS rank
    FROM music
    GROUP BY cover, category
) AS rc ON p.uid = rc.category AND rc.rank <= 4
WHERE p.type = 'category' and p.image IS NULL
GROUP BY p.uid;";
}

function getFourCoverPlaylist()
{
    global $sql;

    $sql = "SELECT p.uid AS playlist_uid,
    COALESCE(MAX(CASE WHEN rc.rank = 1 THEN rc.cover END), null) AS cover_1,
    COALESCE(MAX(CASE WHEN rc.rank = 2 THEN rc.cover END), null) AS cover_2,
    COALESCE(MAX(CASE WHEN rc.rank = 3 THEN rc.cover END), null) AS cover_3,
    COALESCE(MAX(CASE WHEN rc.rank = 4 THEN rc.cover END), null) AS cover_4,
    COUNT(DISTINCT rc.cover) AS total_non_null_cover
FROM playlist p
LEFT JOIN (
    SELECT min(playlist_music.date_add_music_playlist) as date, title, cover, playlist.uid as puid,
ROW_NUMBER() OVER (PARTITION BY playlist.uid ORDER BY date ASC) AS rank
from music
LEFT JOIN playlist_music ON playlist_music.id_music = music.id_music
LEFT JOIN playlist ON playlist_music.id_playlist = playlist.uid
GROUP BY cover, playlist.uid
) AS rc ON p.uid = rc.puid AND rc.rank <= 4
WHERE p.type = 'playlist' and p.image IS NULL
GROUP BY p.uid;";
}

switch ($_GET['method']) {
    case 'four_cover_category':
        getFourCovercategory();
        break;
    case 'four_cover_playlist':
        getFourCoverPlaylist();
    default:
        break;
}

$result = $db->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed");
}

// Create an array to store the data
$data = array();

// Check if there is any data
if ($result->num_rows > 0) {
    // Loop through each row of data
    while ($row = $result->fetch_assoc()) {
        // Clean up the data to handle special characters
        array_walk_recursive($row, function (&$item) {
            if (is_string($item)) {
                $item = htmlentities($item, ENT_QUOTES, 'UTF-8');
            }
        });

        // Add each row to the data array
        $data[] = $row;
    }
}

// Convert the data array to JSON format
$json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Check if JSON conversion was successful
if ($json_data === false) {
    die("JSON encoding failed");
}

// Output the JSON data
echo $json_data;

// Close the dbection
$db->close();