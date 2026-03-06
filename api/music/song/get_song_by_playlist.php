<?php

function getSongByPlaylist($db, $playlistId, $userId)
{

    $query = "CALL GetSongByPlaylist(?,?);";

    $stmtSong = $db->prepare($query);
    $stmtSong->bind_param("ii", $playlistId, $userId);
    $stmtSong->execute();
    $songs = $stmtSong->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}