<?php

require_once __DIR__ . '/../../../utils/utils.php';
require_once __DIR__ . '/../../init.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

function sendArtistJsonError($status, $error, $message)
{
    http_response_code($status);
    echo json_encode([
        'status' => 'error',
        'error' => $error,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        sendArtistJsonError(405, 'method_not_allowed', 'Gunakan method GET.');
    }

    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $role = isset($user['data']['role']) ? (string) $user['data']['role'] : 'free';
    $artistId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$artistId || $artistId < 1) {
        sendArtistJsonError(400, 'invalid_artist_id', 'ID artist tidak valid.');
    }

    $artistStmt = $db->prepare('SELECT id_artist, name FROM cybeat_artists WHERE id_artist = ? LIMIT 1');
    $artistStmt->bind_param('i', $artistId);
    $artistStmt->execute();
    $artist = $artistStmt->get_result()->fetch_assoc();
    $artistStmt->close();

    if (!$artist) {
        sendArtistJsonError(404, 'artist_not_found', 'Artist tidak ditemukan.');
    }

    // Album private hanya terlihat untuk role premium dan admin.
    $privacyCondition = in_array($role, ['premium', 'admin'], true) ? '1 = 1' : 'a.is_private = 0';
    $albumsQuery = "SELECT DISTINCT
            a.uid,
            a.name,
            a.image,
            a.author,
            a.have_disc,
            (
                SELECT dc.bg_color
                FROM dominant_colors dc
                WHERE dc.image_url = a.image
                LIMIT 1
            ) AS bg_color
        FROM cybeat_music_artists ma
        JOIN album_musics am ON am.id_music = ma.id_music
        JOIN albums a ON a.uid = am.id_playlist
        WHERE ma.id_artist = ? AND $privacyCondition
        ORDER BY a.name ASC, a.uid ASC";

    $albumsStmt = $db->prepare($albumsQuery);
    $albumsStmt->bind_param('i', $artistId);
    $albumsStmt->execute();
    $albumRows = $albumsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $albumsStmt->close();

    $albums = array_map(function ($album) {
        return [
            'id' => (int) $album['uid'],
            'type' => 'album',
            'title' => $album['name'],
            'cover' => coverUrlFormatter($album['image']),
            'bg_color' => $album['bg_color'],
            'author' => $album['author'],
            'have_disc' => (int) $album['have_disc'],
        ];
    }, $albumRows);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'artist' => [
                'id_artist' => (int) $artist['id_artist'],
                'name' => $artist['name'],
            ],
            'albums' => $albums,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log('Artist API error: ' . $error->getMessage());
    sendArtistJsonError(500, 'server_error', 'Terjadi kesalahan server.');
} finally {
    if (isset($db)) {
        $db->close();
    }
}

exit;
