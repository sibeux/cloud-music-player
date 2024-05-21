<?php

define('HOST', 'localhost');
define('SIBEUX', 'sibk1922_cbux	');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibk1922_cloud_music');

$conn = new mysqli(HOST, SIBEUX, pass, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

$sql = "SELECT * FROM music ORDER BY title ASC";

if (isset($_GET['_page']) && isset($_GET['_limit'])) {

    $_limit = $_GET['_limit'];
    $_page = $_GET['_page'];

    if ($_page <= 1) {
        $_page = 0;
    } else {
        $_page = ($_page - 1) * $_limit;
    }

    $sql = "SELECT * FROM music ORDER BY title ASC LIMIT $_limit OFFSET $_page";

}

// Query to retrieve data from MySQL
// $sql = "SELECT * FROM music ORDER BY title ASC";
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
?>