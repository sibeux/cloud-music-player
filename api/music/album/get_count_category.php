<?php

function get_count_category($db, $categoryId, $role)
{
    $joinCondition = "";
    $whereCategory = "1=1";

    if ($categoryId !== "6") {
        $joinCondition = "
            JOIN category_albums ca ON ca.album_id = a.uid
            JOIN categories c ON c.category_id = ca.category_id
        ";
        $whereCategory = "c.category_id = ?";
    }

    // Filter privasi berdasarkan role
    $privacyCondition = ($role === 'admin') ? '1=1' : 'a.is_private = 0';

    // Query hanya mengambil satu kolom COUNT
    $query = "SELECT COUNT(m.id_music) AS total
              FROM musics m
              JOIN album_musics am ON am.id_music = m.id_music
              JOIN albums a ON am.id_playlist = a.uid
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