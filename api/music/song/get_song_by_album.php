<?php

require_once __DIR__ . '/queries/get_song_by_album.php';

function getSongByAlbum($db, $albumId, $role)
{
    $songs = getSongsByAlbum($db, (int) $albumId, $role);
    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}
