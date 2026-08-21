<?php

function getSongByPlaylist($db, $playlistId, $userId)
{

    $query = "CALL GetSongByPlaylist(?,?);";

    $stmtSong = $db->prepare($query);
    $stmtSong->bind_param("ii", $playlistId, $userId);
    $stmtSong->execute();
    $songs = $stmtSong->get_result()->fetch_all(MYSQLI_ASSOC);
    $songDTOs = array_map(function($song) {
        return new SongDTO($song);
    }, $songs);

    echo json_encode([
        "status" => "success",
        "data" => $songDTOs
    ]);
}