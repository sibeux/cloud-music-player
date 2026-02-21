<?php

function get_album($db, $userId, $role = 'user')
{
    $query = "SELECT 
        a.uid, a.name, a.image, a.author, a.have_disc,
        (
            -- Ambil waktu terakhir kali salah satu lagu dari album ini diputar
            SELECT MAX(rm.played_at) 
            FROM recent_musics rm
            WHERE rm.recentable_album_id = a.uid
                AND rm.recentable_album_type = 'album'
        ) AS played_at,
        ap.created_at AS pin_at, -- Waktu album di-pin
        (
            -- Ambil waktu album dibuat
            SELECT MAX(am3.date_created) 
            FROM album_musics am3
            WHERE am3.id_playlist = a.uid
        ) AS date_created
    FROM `albums` a
    -- Ambil data pin album (jika ada), tidak wajib ada (LEFT JOIN)
    LEFT JOIN `album_pins` ap ON ap.pinnable_album_id = a.uid 
        AND ap.pinnable_album_type = 'album' 
        AND ap.user_id = ? -- Filter by user
    WHERE (? = 'admin' OR a.is_private = 1) -- Jika admin, lewati filter is_private
    ORDER BY 
        -- Album yang dipin muncul duluan (NULL ke bawah)
        pin_at IS NULL ASC,
        -- Pin terlama di atas
        pin_at ASC,
        -- Lagu terakhir diputar, terbaru di atas
        played_at DESC,
        -- Lagu terbaru ditambahkan, terbaru di atas
        date_created DESC;";

    $stmt = $db->prepare($query);
    $stmt->bind_param("is", $userId, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}