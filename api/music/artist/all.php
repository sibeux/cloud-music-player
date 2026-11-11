<?php

require_once __DIR__ . '/../../init.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

function sendAllArtistsJsonError($status, $error, $message)
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
        sendAllArtistsJsonError(405, 'method_not_allowed', 'Gunakan method GET.');
    }

    // Endpoint ini hanya membaca katalog publik; token boleh disertakan, tetapi tidak wajib.
    $auth = new BearerAuth($secretKey);
    $auth->validate(false);

    $query = "SELECT
            a.id_artist,
            a.name,
            COUNT(DISTINCT ma.id_music) AS song_count
        FROM cybeat_artists AS a
        LEFT JOIN cybeat_music_artists AS ma ON ma.id_artist = a.id_artist
        GROUP BY a.id_artist, a.name
        ORDER BY a.name ASC, a.id_artist ASC";

    $result = $db->query($query);
    $artists = [];
    while ($row = $result->fetch_assoc()) {
        $artists[] = [
            'id_artist' => (int) $row['id_artist'],
            'name' => $row['name'],
            'song_count' => (int) $row['song_count'],
        ];
    }
    $result->free();

    echo json_encode([
        'status' => 'success',
        'count' => count($artists),
        'data' => $artists,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log('All artists API error: ' . $error->getMessage());
    sendAllArtistsJsonError(500, 'server_error', 'Terjadi kesalahan server.');
} finally {
    if (isset($db)) {
        $db->close();
    }
}

exit;
