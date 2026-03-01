<?php

global $ffprobePath;
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/read_codec.php';
require_once __DIR__ . '/../../../api/image-dominant-color/get_color.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;

    if (isset($_POST['music_id']) && isset($_POST['codec_exist']) && isset($_POST['music_url']) && isset($_POST['dominant_color_exist']) && isset($_POST['image_url'])) {
        $codec = null;
        $dominant_color = null;
        $image_url = $_POST['image_url'];
        $music_id = $_POST['music_id'];
        $music_url = $_POST['music_url'];
        $codec_exist = $_POST['codec_exist'];
        $dominant_color_exist = $_POST['dominant_color_exist'];
        $albumId = $_POST['album_id'];
        $albumType = $_POST['album_type'];

        // Eksekusi query untuk 'recents_music'
        if ($userId != 0){
            $stmt_recents = $db->prepare("INSERT INTO recents_musics (uid_music, user_id, recentable_album_id, recentable_album_type) VALUES (?, ?, ?, ?)");
            $stmt_recents->bind_param("iiis", $music_id, $userId, $albumId, $albumType);
            if (!$stmt_recents->execute()) {
                die("Error inserting recents: " . $stmt_recents->error);
            }
            $stmt_recents->close();
        }

        // Eksekusi query untuk 'metadata_music'
        // Cek dulu apakah perlu dilakukan read codec?
        if ($codec_exist == 'false'){
            $codec = checkCodecAudio($music_id, $music_url, $db, $ffprobePath);
        }

        // Dapatkan dominant color dari cover
        if ($dominant_color_exist == 'false'){
            // ini warning karena diambil dari repo lain.
            $dominant_color = getDominantColors($image_url, $db);
        }
        
        // Execution query for 'delete'
        $delete_sql = "DELETE FROM recents_musics
                    WHERE uid_recents NOT IN (
                        SELECT uid_recents FROM (
                            SELECT uid_recents FROM recents_musics WHERE user_id = ? ORDER BY played_at DESC LIMIT 500
                        ) AS last_500
                    )";
        $stmt_delete = $db->prepare($delete_sql);
        $stmt_delete->bind_param("i", $userId);
        if (!$stmt_delete->execute()) {
            die("Error deleting old recents: " . $stmt_delete->error);
        }
        $stmt_delete->close();

        // echo json response
        sendJsonResponse([
            "success" => true,
            "metadata" => "Metadata success processed dan saved.",
            "recents" => "Recent music successfully added.",
            "codec" => $codec,
            "dominant_color" => $dominant_color,
        ]);
    } else {
        // Add response if no ada POST data
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Error: music_id is not set."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
} finally{
    if (isset($db)) {
        $db->close();
    }
}
exit;