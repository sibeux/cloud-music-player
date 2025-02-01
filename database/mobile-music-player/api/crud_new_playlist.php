<?php

include './connection.php';

$method = '';
$sql = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = $_GET['method'] ?? '';
}

function createPlaylist($db){
    if (
        $stmt = $db->prepare("INSERT INTO playlist (uid, name, image, type, author, pin, date_pin, date, editable) 
    values (NULL, ?, NULL, 'playlist', 'Nasrul Wahabi', 'false', NULL, NOW(), 'true')")
    ) {
        $name = $_POST['name_playlist'];

        $stmt->bind_param(
            's',
            $name
        );

        if ($stmt->execute()) {
            $response = ["status" => "success"];
        } else {
            $response = [
                "status" => "error",
                "message" => "Failed to execute the query.",
                "error" => $stmt->error // Pesan error untuk debugging
            ];
        }

        $stmt->close();
        echo json_encode($response);
    } else {
        $response = ["status" => "failed"];
        echo json_encode($response);
        echo 'Could not prepare statement!';
    }
}

function deletePlaylist($db)
{
    if (
        $stmt = $db->prepare("DELETE FROM playlist WHERE playlist.uid = ?")
    ) {
        $uid = $_POST['playlist_uid'];

        $stmt->bind_param(
            'i',
            $uid
        );

        if ($stmt->execute()) {
            $response = ["status" => "success"];
        } else {
            $response = [
                "status" => "error",
                "message" => "Failed to execute the query.",
                "error" => $stmt->error // Pesan error untuk debugging
            ];
        }

        $stmt->close();
        echo json_encode($response);
    } else {
        $response = ["status" => "failed"];
        echo json_encode($response);
        echo 'Could not prepare statement!';
    }
}

switch ($method) {
    case 'create':
        createPlaylist($db);
        break;
    case 'delete':
        deletePlaylist($db);
    default:
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query($sql);

    // Check if the query was successful
    if (!$result) {
        die("Query failed: " . (is_object($db) ? $db->error : 'Database connection error'));
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
}

$db->close();