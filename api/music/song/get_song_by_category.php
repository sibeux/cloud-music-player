<?php

require_once __DIR__ . '/queries/get_song_by_category.php';

function getSongByCategory($db, $categoryId, $role, $search = '', $sortBy = 'default', $sortOrder = 'asc')
{
    $songs = getSongsByCategory($db, $categoryId, $role, $search, $sortBy, $sortOrder);
    echo json_encode([
        "status" => "success",
        "data" => $songs
    ]);
}
