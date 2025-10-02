<?php

include '.././../db.php';

$sql = "SELECT 
    m.*, p.name as album
    FROM music m
    JOIN album_music on album_music.id_music = m.id_music
    JOIN playlist p ON album_music.id_playlist = p.uid
    ORDER BY m.title ASC";

if (isset($_GET['_page']) && isset($_GET['_limit'])) {

    $_limit = $_GET['_limit'];
    $_page = $_GET['_page'];

    if ($_page <= 1) {
        $_page = 0;
    } else {
        $_page = ($_page - 1) * $_limit;
    }

    $sql = "SELECT m.*, p.name as album
    FROM music m
    JOIN album_music on album_music.id_music = m.id_music
    JOIN playlist p ON album_music.id_playlist = p.uid
    ORDER BY m.title ASC LIMIT $_limit OFFSET $_page";

}

// Query to retrieve data from MySQL
// $sql = "SELECT * FROM music ORDER BY title ASC";
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

// Close the dbection
$db->close();