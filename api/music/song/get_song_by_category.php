<?php

function getSongByCategory($db, $categoryId, $role){
    // Pastikan join hanya terjadi jika kategori spesifik (!= 6)
    $joinCondition = "";
    $whereCategory = "1=1"; // Default jika kategori 6 (tampilkan semua)

    if ($categoryId !== "6") {
        $joinCondition = "
            JOIN category_albums ca on ca.album_id = a.uid
            JOIN categories c on c.category_id = ca.category_id
        ";
        $whereCategory = "c.category_id = ?";
    }

    $privacyCondition = ($role === 'admin') ? '1=1' : 'a.is_private = 0';

    $query = "SELECT 
        m.id_music, m.link_gdrive, m.title, m.artist, m.cover, m.disc_number, m.uploader, m.is_suspicious,
        a.name as album,
        mm.metadata_id_music, mm.codec_name, mm.music_quality, mm.sample_rate, mm.bit_rate, mm.bits_per_raw_sample,
        dc.bg_color, dc.text_color,
        cache_musics.cache_music_id
        FROM musics m
        JOIN album_musics am on am.id_music = m.id_music
        JOIN albums a on am.id_playlist = a.uid
        $joinCondition
        LEFT JOIN metadata_musics mm ON m.id_music = mm.metadata_id_music
        LEFT JOIN cache_musics ON m.id_music = cache_musics.cache_music_id
        LEFT JOIN dominant_colors dc on m.cover = dc.image_url
        WHERE $whereCategory AND $privacyCondition
        ORDER BY m.title ASC;";

    $stmtSong = $db->prepare($query);

    // Bind parameter hanya jika bukan kategori 6
    if ($categoryId !== "6") {
        $stmtSong->bind_param("i", $categoryId);
    }

    $stmtSong->execute();
    $result = $stmtSong->get_result();
    $songs = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}