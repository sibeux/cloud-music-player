<?php
session_start();
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../stream_drive.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;
    $userRole = isset($user['data']['role']) ? $user['data']['role'] : 'user';

    $musicId = isset($_GET['music_id']) ? $_GET['music_id'] : null;
    $fileType = isset($_GET['file_type']) ? $_GET['file_type'] : "audio";

    $sql = "SELECT m.link_gdrive, a.is_private 
            FROM musics m
            JOIN album_musics am ON m.id_music = am.id_music
            JOIN albums a ON am.id_playlist = a.uid
            WHERE m.id_music = ? 
            LIMIT 1;";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $musicId);
    $stmt->execute();
    $result = $stmt->get_result();
    $music = $result->fetch_assoc();
    $stmt->close();

    if (!$music) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "error" => "music_not_found",
            "message" => "Music not found",
        ]);
        die();
    }

    // Cek Akses
    if ($music['is_private'] == 1 && $userRole === 'user') {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "error" => "access_denied",
            "message" => "Akses ditolak: Konten Premium",
        ]);
        die();
    }

    // Cek source of stream
    $musicUrl = $music['link_gdrive'];
    var_dump($musicUrl);
    if (stripos($musicUrl, 'drive.google.com') !== false) {
        streamingMusicFromGdrive($db, $musicId, $musicUrl, $fileType, $allApiData, $ffprobePath);
    } else if (stripos($musicUrl, 'cdncloudflare/') !== false) {
        
    } else {

    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
}