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

    $query = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.uploader, m.is_suspicious,
        a.name as album,
        mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color,
        cache_musics.cache_music_id
        FROM musics m
        /* 
        Ini bentuk komen multi-line dan lebih aman.
        JOIN playlist ON music.album LIKE CONCAT('%', TRIM(BOTH '\r\n' FROM playlist.name), '%') */
        JOIN album_musics am on am.id_music = m.id_music
        JOIN albums a on am.id_playlist = a.uid
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc on m.cover = dc.image_url
        WHERE a.uid = ?
        ORDER BY m.title ASC;";

    $stmtSong = $db->prepare($query);
    $stmtSong->bind_param("i", $albumId);
    $stmtSong->execute();
    $songs = $stmtSong->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}