<?php

include './connection.php';

$sql = "SELECT * FROM playlist ORDER BY pin";

if (isset($_GET['sort']) && isset($_GET['filter'])) {
    $sort = $_GET['sort'];
    $filter = $_GET['filter'] == '' ? null : $_GET['filter'];

    if ($sort == 'uid') {
        $sql = "SELECT * FROM playlist WHERE LENGTH('$filter') = 0 OR type = '$filter' ORDER BY pin, date_pin asc, date desc";
    } else if ($sort == 'title') {
        $sql = "SELECT * FROM playlist WHERE LENGTH('$filter') = 0 OR type = '$filter' ORDER BY pin, date_pin asc, name asc";
    }
}

if (isset($_GET['type']) && isset($_GET['uid'])) {

    $type = $_GET['type'];
    $uid = $_GET['uid'];

    if ($type == 'category' && $uid == 481) {
        $sql = "SELECT * FROM music ORDER BY title ASC";
    }

    if ($type == 'category' && $uid != 481) {
        $sql = "SELECT * FROM music
        join playlist on music.category like playlist.uid
        WHERE music.category = '$uid'
        ORDER BY music.title ASC";
    }

    if ($type == 'album') {
        $sql = "SELECT * FROM music 
        JOIN playlist ON music.album LIKE CONCAT('%', TRIM(playlist.name), '%') 
        WHERE playlist.uid = '$uid'
        ORDER BY title ASC";
    }

    if ($type == 'playlist') {
        $sql = "SELECT * FROM playlist_music 
        JOIN music on playlist_music.id_music = music.id_music 
        join playlist on playlist_music.id_playlist = playlist.uid 
        WHERE playlist.uid = '$uid' 
        ORDER BY date_add_music_playlist ASC";
    }

    if ($type == 'favorite') {
        $sql = "SELECT * FROM music WHERE favorite = '1' ORDER BY title ASC";
    }

}

if (isset($_GET['count_favorite'])) {
    $sql = "SELECT COUNT(*) as count_favorite FROM music where favorite = '1'";
}

if (isset($_GET['count_category'])) {
    $sql = "SELECT playlist.uid, COALESCE(COUNT(music.category), 0) AS type_count
    FROM playlist
    LEFT JOIN music ON music.category = playlist.uid
    WHERE playlist.type = 'category'
    GROUP BY playlist.uid
    ORDER BY playlist.uid ASC";
}

if (isset($_GET['play_playlist'])) {
    $uid = $_GET['play_playlist'];

    $sql = "UPDATE playlist SET date = NOW() WHERE uid = '$uid'";
}

if (isset($_GET['recents_music'])) {
    $sql = "SELECT * FROM recents_music join music on music.id_music = recents_music.uid_music ORDER BY played_at DESC";
}

// Query to retrieve data from MySQL
$result = $db->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $db->error);
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

// Close the connection
$db->close();