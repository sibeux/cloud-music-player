<?php

function get_count_category($db, $categoryId, $role)
{
    $joinCondition = "";
    $whereCategory = "1=1";

    // Sudah kubilang, hati-hati sama operator `!==` - karena dia tidak bisa membandingkan string dan integer.
    // Harus exact match.
    if ($categoryId !== 6) {
        $joinCondition = "
            JOIN category_albums ca on ca.album_id = a.uid
            JOIN categories c on c.category_id = ca.category_id
        ";
        $whereCategory = "c.category_id = ?";
    }

    // Filter privasi berdasarkan role
    $privacyCondition = ($role === 'admin') ? '1=1' : 'a.is_private = 0';

    // Query hanya mengambil satu kolom COUNT
    $query = "SELECT COUNT(m.id_music) AS total
              FROM musics m
              JOIN album_musics am on am.id_music = m.id_music
              JOIN albums a on am.id_playlist = a.uid
              $joinCondition
              WHERE $whereCategory AND $privacyCondition";

    $stmt = $db->prepare($query);

    if ($categoryId !== 6) {
        $stmt->bind_param("i", $categoryId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['total'];
}