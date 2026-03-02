<?php

function get_count_category($db, $categoryId, $role)
{
    $joinCondition = "";
    $whereCategory = "1=1";

    // Jika kategori BUKAN 6, kita batasi berdasarkan relasi category -> album -> music
    if ($categoryId !== "6") {
        $joinCondition = "
            JOIN album_musics am ON am.id_music = m.id_music
            JOIN albums a ON am.id_playlist = a.uid
            JOIN category_albums ca ON ca.album_id = a.uid
        ";
        $whereCategory = "ca.category_id = ?";
    }

    // Filter privasi: Admin bisa lihat semua, User biasa hanya yang non-private
    // Jika Category 6 (semua lagu), kita cek privasi hanya jika lagu tersebut punya album
    $privacyCondition = ($role === 'admin') ? '1=1' : "(a.is_private = 0 OR a.is_private IS NULL)";

    $query = "SELECT COUNT(DISTINCT m.id_music) AS total
              FROM musics m
              LEFT JOIN album_musics am ON am.id_music = m.id_music
              LEFT JOIN albums a ON am.id_playlist = a.uid
              $joinCondition
              WHERE $whereCategory AND $privacyCondition";

    $stmt = $db->prepare($query);

    if ($categoryId !== "6") {
        $stmt->bind_param("i", $categoryId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['total'];
}