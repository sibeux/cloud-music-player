<?php

require_once __DIR__ . '/../../init.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;
    $userRole = isset($user['data']['role']) ? $user['data']['role'] : 'user';

    $musicId = isset($_GET['musicId']) ? $_GET['musicId'] : null;

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

    if (!$music) {
        http_response_code(404);
        die("Music not found");
    }

    // Cek Akses
    if ($music['is_private'] == 1 && $userRole === 'user') {
        http_response_code(403);
        die("Akses ditolak: Konten Premium");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
}