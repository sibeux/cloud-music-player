<?php

function getFourCoverPlaylist($db, $playlistId){
    $query = "SELECT 
        p.playlist_id,
        MAX(CASE WHEN rc.rank = 1 THEN rc.cover END) AS cover_1,
        MAX(CASE WHEN rc.rank = 2 THEN rc.cover END) AS cover_2,
        MAX(CASE WHEN rc.rank = 3 THEN rc.cover END) AS cover_3,
        MAX(CASE WHEN rc.rank = 4 THEN rc.cover END) AS cover_4,
        -- Menghitung jumlah cover yang tidak NULL hasil dari join
        COUNT(rc.cover) AS total_non_null_cover
    FROM playlists p
    LEFT JOIN (
        SELECT 
            pm.id_playlist, 
            m.cover,
            ROW_NUMBER() OVER (PARTITION BY pm.id_playlist ORDER BY MIN(pm.created_at) ASC) as rank
        FROM playlist_musics pm
        JOIN musics m ON pm.id_music = m.id_music
        GROUP BY pm.id_playlist, m.cover 
    ) AS rc ON p.playlist_id = rc.id_playlist AND rc.rank <= 4
    WHERE p.playlist_id = ? AND p.cover IS NULL
    GROUP BY p.playlist_id;";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $playlistId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data;
}