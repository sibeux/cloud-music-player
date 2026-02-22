<?php

function get_category($db, $userId)
{
    $query = "SELECT 
        c.category_id, c.name, c.cover, c.created_at,
        (
            -- Ambil waktu terakhir kali salah satu lagu dari kategori ini diputar
            SELECT MAX(rm.played_at) 
            FROM recent_musics rm
            WHERE rm.recentable_album_id = c.category_id
                AND rm.recentable_album_type = 'category'
        ) AS played_at,
        ap.created_at AS pin_at -- Waktu album di-pin
    FROM `categories` c
    LEFT JOIN `album_pins` ap ON ap.pinnable_album_id = c.category_id
        AND ap.pinnable_album_type = 'category'
        AND ap.user_id = ? -- Filter by user
    ORDER BY
        pin_at IS NULL ASC,
        pin_at ASC,
        played_at DESC,
        c.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    // Pakai while agar semua data bisa masuk ke array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['category_id'],
            'type' => 'category',
            'title' => $row['name'],
            'cover' => $row['cover'],
            'author' => null,
            'played_at' => $row['played_at'],
            'pin_at' => $row['pin_at'],
            'have_disc' => 0,
            'created_at' => $row['created_at'],
        ];
    }
    return $data;
}