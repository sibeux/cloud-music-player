<?php

require_once __DIR__ . '/get_four_cover.php';

function get_playlist($db, $userId)
{
    $query = "SELECT
        p.playlist_id, p.title, p.cover, p.created_at,
        u.name as author,
        dc.bg_color,
        (
            -- Ambil waktu terakhir kali salah satu lagu dari playlist ini diputar
            SELECT MAX(rm.played_at)
            FROM recent_musics rm
            WHERE rm.recentable_album_id = p.playlist_id
                AND rm.recentable_album_type = 'playlist'
                AND rm.user_id = ?
        ) AS played_at,
        ap.created_at AS pin_at -- Waktu album di-pin
    FROM `playlists` p
    JOIN users u on u.user_id = p.user_id
    LEFT JOIN dominant_colors dc on p.cover = dc.image_url
    LEFT JOIN `album_pins` ap ON ap.pinnable_album_id = p.playlist_id
        AND ap.pinnable_album_type = 'playlist'
        AND ap.user_id = ? -- Filter by user
    WHERE p.user_id = ?
    ORDER BY
        pin_at IS NULL ASC,
        pin_at ASC,
        played_at DESC,
        p.created_at DESC;";

    $stmt = $db->prepare($query);
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    // Pakai while agar semua data bisa masuk ke array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $cover = $row['cover'];
        if (empty($cover)) {
            $fourCover = getFourCoverPlaylist($db, $row['playlist_id']);
            $cover = $fourCover;
        }
        $data[] = [
            'id' => $row['playlist_id'],
            'type' => 'playlist',
            'title' => $row['title'],
            'cover' => $cover,
            'bg_color' => $row['bg_color'],
            'author' => $row['author'],
            'played_at' => $row['played_at'],
            'pin_at' => $row['pin_at'],
            'have_disc' => 0,
            'created_at' => $row['created_at'],
        ];
    }
    return $data;
}