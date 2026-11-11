<?php

function previewArtistMerge($db, $sourceArtistId, $targetArtistId)
{
    if ($sourceArtistId < 1 || $targetArtistId < 1 || $sourceArtistId === $targetArtistId) {
        sendAdminJsonError(422, 'invalid_artist_selection', 'Pilih dua Artist yang berbeda.');
    }

    $stmt = $db->prepare(
        'SELECT id_artist, name FROM cybeat_artists WHERE id_artist IN (?, ?) ORDER BY id_artist ASC'
    );
    $stmt->bind_param('ii', $sourceArtistId, $targetArtistId);
    $stmt->execute();
    $artists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $byId = [];
    foreach ($artists as $artist) {
        $byId[(int) $artist['id_artist']] = $artist;
    }

    if (!isset($byId[$sourceArtistId]) || !isset($byId[$targetArtistId])) {
        sendAdminJsonError(404, 'artist_not_found', 'Salah satu Artist tidak ditemukan. Muat ulang daftar Artist.');
    }

    $sourceCount = artistMergeLinkCount($db, $sourceArtistId);
    $targetCount = artistMergeLinkCount($db, $targetArtistId);

    $sharedStmt = $db->prepare(
        'SELECT COUNT(*) AS shared_count
         FROM cybeat_music_artists AS source
         INNER JOIN cybeat_music_artists AS target ON target.id_music = source.id_music
         WHERE source.id_artist = ? AND target.id_artist = ?'
    );
    $sharedStmt->bind_param('ii', $sourceArtistId, $targetArtistId);
    $sharedStmt->execute();
    $sharedCount = (int) $sharedStmt->get_result()->fetch_assoc()['shared_count'];
    $sharedStmt->close();

    return [
        'source' => [
            'id_artist' => $sourceArtistId,
            'name' => $byId[$sourceArtistId]['name'],
            'song_count' => $sourceCount,
        ],
        'target' => [
            'id_artist' => $targetArtistId,
            'name' => $byId[$targetArtistId]['name'],
            'song_count' => $targetCount,
        ],
        'shared_song_count' => $sharedCount,
        'result_song_count' => $sourceCount + $targetCount - $sharedCount,
    ];
}

function artistMergeLinkCount($db, $artistId)
{
    $stmt = $db->prepare('SELECT COUNT(*) AS song_count FROM cybeat_music_artists WHERE id_artist = ?');
    $stmt->bind_param('i', $artistId);
    $stmt->execute();
    $count = (int) $stmt->get_result()->fetch_assoc()['song_count'];
    $stmt->close();

    return $count;
}
