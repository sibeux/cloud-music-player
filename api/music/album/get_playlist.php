<?php

function get_playlist($db, $userId)
{
    $query = "SELECT 
        p.playlist_id, p.title, p.cover,
        u.name as author,
        (
            -- Ambil waktu terakhir kali salah satu lagu dari playlist ini diputar
            SELECT MAX(rm.played_at) 
            FROM recent_musics rm
            WHERE rm.recentable_album_id = p.playlist_id
                AND rm.recentable_album_type = 'playlist'
        ) AS played_at,
        ap.created_at AS pin_at -- Waktu album di-pin
    FROM `playlists` p
    JOIN users u on u.user_id = p.user_id
    LEFT JOIN `album_pins` ap ON ap.pinnable_album_id = p.playlist_id
        AND ap.pinnable_album_type = 'playlist'
        AND ap.user_id = ? -- Filter by user
    ORDER BY
        pin_at IS NULL ASC,
        pin_at ASC,
        played_at DESC,
        p.created_at DESC;";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    // Pakai while agar semua data bisa masuk ke array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['playlist_id'],
            'type' => 'playlist',
            'title' => $row['title'],
            'cover' => $row['cover'],
            'author' => $row['author'],
            'played_at' => $row['played_at'],
            'pin_at' => $row['pin_at'],
            'have_disc' => 0,
            'created_at' => $row['created_at'],
        ];
    }
    return $data;
}