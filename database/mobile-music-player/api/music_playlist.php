<?php

include './connection.php';

$method = '';
$sql = '';
$data = [];

$id_music = '';
$toAdd = [];
$toRemove = [];
$id_music_playlist = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mendapatkan data JSON dari body request
    $input = file_get_contents('php://input');

    // Mengubah JSON menjadi array PHP
    $data = json_decode($input, true);

    // Cek apakah data berhasil di-decode
    if ($data === null) {
        // Jika gagal decode JSON, tangani error
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Invalid JSON data']);
        exit;
    }

    $method = $data['method'] ?? '';
    $id_music = $data['id_music'] ?? '';
    $toAdd = $data['to_add'] ?? [];
    $toRemove = $data['to_remove'] ?? [];
    $id_playlist_music = $data['id_playlist_music'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = $_GET['method'] ?? '';
}

function getMusicOnPlaylist($id_music)
{
    global $sql;

    $sql = "SELECT * FROM `playlist_music` where playlist_music.id_music = $id_music;";
}

function updateMusicOnPlaylist($db)
{
    global $id_music;
    global $toAdd;
    global $toRemove;

    if (!empty($toAdd)) {
        // Siapkan SQL dengan placeholder untuk parameter binding
        $sql = "INSERT INTO `playlist_music` (`id_playlist_music`, `id_music`, `id_playlist`, `date_add_music_playlist`) 
            VALUES (?, ?, ?, NOW())";

        if ($stmt = $db->prepare($sql)) {
            foreach ($toAdd as $id) {
                $id_playlist_music = NULL; // ID auto-increment
                $stmt->bind_param("iii", $id_playlist_music, $id_music, $id);

                if (!$stmt->execute()) {
                    $response = [
                        "status" => "error",
                        "message" => "Failed to execute the query.",
                        "error" => $stmt->error
                    ];
                    echo json_encode($response);
                    exit; // Keluar jika terjadi error
                }
            }
            $stmt->close();
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Could not prepare statement!"]);
        }
    }

    if (!empty($toRemove)) {
        $sql = "DELETE FROM `playlist_music` WHERE `id_music` = $id_music AND `id_playlist` IN (" . implode(',', $toRemove) . ");";

        // Eksekusi query
        if ($stmt = $db->prepare($sql)) {
            if (
                $stmt->execute()
            ) {
                $response = ["status" => "success", "message" => "Data has been deleted."];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Failed to execute the query.",
                    "error" => $stmt->error // Pesan error untuk debugging
                ];
            }
            $stmt->close();
            if (empty($toAdd)) {
                echo json_encode($response);
            }
        } else {
            $response = ["status" => "failed"];
            echo json_encode($response);
            echo 'Could not prepare statement!';
        }
    }
}

function deleteMusicOnPlaylist($db): void
{
    global $id_playlist_music;

    $sql = "DELETE FROM `playlist_music` WHERE `id_playlist_music` = $id_playlist_music;";

    // Eksekusi query
    if ($stmt = $db->prepare($sql)) {
        if (
            $stmt->execute()
        ) {
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
    case 'get_music_on_playlist':
        getMusicOnPlaylist($_GET['id_music']);
        break;
    case 'update_music_on_playlist':
        updateMusicOnPlaylist($db);
        break;
    case 'delete_music_on_playlist':
        deleteMusicOnPlaylist($db);
        break;
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