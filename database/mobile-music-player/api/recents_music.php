<?php

global $ffprobePath, $db;
include './connection.php';
require_once __DIR__ . '/read_codec.php';

if (isset($_POST['music_id']) && isset($_POST['codec_exist']) && isset($_POST['music_url'])) {
    $codec = null;

    // 1. Eksekusi query untuk 'recents_music'
    $stmt_recents = $db->prepare("INSERT INTO recents_music (uid_music, played_at) VALUES (?, NOW())");
    $stmt_recents->bind_param("i", $_POST['music_id']); // i = integer
    if (!$stmt_recents->execute()) {
        die("Error inserting recents: " . $stmt_recents->error);
    }
    $stmt_recents->close();

    // 2. Eksekusi query untuk 'metadata_music'
    // Cek dulu apakah perlu dilakukan read codec?
    if ($_POST['codec_exist'] == 'false'){
        $codec = checkCodecAudio($_POST['music_id'], $_POST['music_url'], $db, $ffprobePath);
    }
    
    // 3. Execution query for 'delete'
    $delete_sql = "DELETE FROM recents_music
                   WHERE uid_recents NOT IN (
                       SELECT uid_recents FROM (
                           SELECT uid_recents FROM recents_music ORDER BY played_at DESC LIMIT 500
                       ) AS last_500
                   )";
    if (!$db->query($delete_sql)) {
        die("Error deleting old recents: " . $db->error);
    }

    // echo json response
    sendJsonResponse([
        "success" => true,
        "metadata" => "Metadata success processed dan saved.",
        "recents" => "Recent music successfully added.",
        "codec" => $codec,
    ]);
} else {
    // Add response if no ada POST data
    http_response_code(400);
    echo "Error: music_id is not set.";
}

// Close connection in the end
$db->close();