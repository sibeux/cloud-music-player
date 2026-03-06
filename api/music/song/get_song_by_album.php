<?php

function getSongByAlbum($db, $albumId, $role){
    // Cek status album terlebih dahulu
    $stmt = $db->prepare("SELECT is_private FROM albums WHERE uid = ?");
    $stmt->bind_param("i", $albumId);
    $stmt->execute();
    $result = $stmt->get_result();
    $album = $result->fetch_assoc();

    if (!$album) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "error" => "not_found",
            "message" => "Album tidak ditemukan"
        ]);
        return;
    }

    if ($album['is_private'] == 1 && $role == 'user') {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "error" => "no_access",
            "message" => "Anda tidak memiliki akses untuk melihat album ini"
        ]);
        return;
    }

    // Panggil via Stored Procedure
    $query = "CALL GetSongByAlbum(?);";

    $stmtSong = $db->prepare($query);
    $stmtSong->bind_param("i", $albumId);
    $stmtSong->execute();
    $songs = $stmtSong->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}