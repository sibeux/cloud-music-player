<?php

require_once __DIR__ . '/queries/get_song_by_playlist.php';

function getSongByPlaylist($db, $playlistId, $userId, $search = '', $sortBy = 'default', $sortOrder = 'asc')
{
    $songs = getSongsByPlaylist($db, (int) $playlistId, (int) $userId, $search, $sortBy, $sortOrder);
    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}
