<?php

include './connection.php';

$sql = "SELECT * FROM playlist ORDER BY pin";

if (isset($_GET['sort']) && isset($_GET['filter'])) {
    $sort = $_GET['sort'];
    $filter = $_GET['filter'] == '' ? '1=1' : $_GET['filter'];

    if ($sort == 'uid') {
        $sql = "SELECT * FROM playlist WHERE ('$filter' IS NULL OR type = '$filter') ORDER BY pin, date_pin asc, uid desc";
    } else if ($sort == 'title') {
        $sql = "SELECT * FROM playlist WHERE ('$filter' IS NULL OR type = '$filter') ORDER BY pin, date_pin asc, name asc";
    }
}

if (isset($_GET['type']) && isset($_GET['uid'])) {

    $type = $_GET['type'];
    $uid = $_GET['uid'];

    if ($type == 'category') {
        $sql = "SELECT * FROM music
join playlist on music.category like playlist.uid
WHERE music.category = '$uid'
ORDER BY music.title ASC";
    }

    if ($type == 'album') {
        $sql = "SELECT * FROM music 
        join playlist on music.album like CONCAT('%', playlist.name, '%') 
        where playlist.uid = '$uid'
        ORDER BY title ASC";
    }

}


// Query to retrieve data from MySQL
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
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
$conn->close();