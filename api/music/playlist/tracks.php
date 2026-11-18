<?php
require_once __DIR__ . '/../song/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    [$userId, $role] = authenticateSongUser($secretKey);
    if ($userId <= 0) {
        sendSongJsonError(401, 'unauthorized', 'Sign in to manage playlist songs.');
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['POST', 'DELETE'], true)) {
        header('Allow: POST, DELETE');
        sendSongJsonError(405, 'method_not_allowed', 'Use POST to add and DELETE to remove a song.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        sendSongJsonError(400, 'invalid_json', 'A JSON request body is required.');
    }

    $playlistId = filter_var($input['playlist_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$playlistId || $playlistId <= 0) {
        sendSongJsonError(422, 'invalid_ids', 'A valid playlist_id is required.');
    }

    $songIds = [];
    if ($method === 'POST') {
        $songId = filter_var($input['song_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$songId || $songId <= 0) {
            sendSongJsonError(422, 'invalid_ids', 'A valid song_id is required.');
        }
        $songIds = [(int) $songId];
    } else {
        $requestedIds = $input['song_ids'] ?? (isset($input['song_id']) ? [$input['song_id']] : []);
        if (!is_array($requestedIds) || count($requestedIds) === 0 || count($requestedIds) > 500) {
            sendSongJsonError(422, 'invalid_ids', 'Provide between 1 and 500 song ids.');
        }
        foreach ($requestedIds as $requestedId) {
            $validId = filter_var($requestedId, FILTER_VALIDATE_INT);
            if (!$validId || $validId <= 0) {
                sendSongJsonError(422, 'invalid_ids', 'All song ids must be valid positive integers.');
            }
            $songIds[] = (int) $validId;
        }
        $songIds = array_values(array_unique($songIds));
    }

    $db->begin_transaction();
    try {
        $playlist = $db->prepare('SELECT playlist_id FROM playlists WHERE playlist_id = ? AND user_id = ? FOR UPDATE');
        $playlist->bind_param('ii', $playlistId, $userId);
        $playlist->execute();
        $owned = (bool) $playlist->get_result()->fetch_row();
        $playlist->close();
        if (!$owned) {
            $db->rollback();
            sendSongJsonError(404, 'playlist_not_found', 'Playlist not found.');
        }

        if ($method === 'POST') {
            $access = $db->prepare("SELECT 1
                FROM musics m
                JOIN album_musics am ON am.id_music = m.id_music
                JOIN albums a ON a.uid = am.id_playlist
                WHERE m.id_music = ? AND (a.is_private = 0 OR ? IN ('premium', 'admin'))
                LIMIT 1");
            $access->bind_param('is', $songId, $role);
            $access->execute();
            $canAdd = (bool) $access->get_result()->fetch_row();
            $access->close();
            if (!$canAdd) {
                $db->rollback();
                sendSongJsonError(404, 'song_not_found', 'Song not found or not available to this account.');
            }

            $existing = $db->prepare('SELECT id_playlist_music FROM playlist_musics WHERE id_playlist = ? AND id_music = ? LIMIT 1');
            $existing->bind_param('ii', $playlistId, $songId);
            $existing->execute();
            $alreadyAdded = (bool) $existing->get_result()->fetch_row();
            $existing->close();
            if ($alreadyAdded) {
                $db->commit();
                echo json_encode(['status' => 'success', 'message' => 'Song is already in this playlist.']);
                exit;
            }

            $add = $db->prepare('INSERT INTO playlist_musics (id_music, id_playlist, created_at) VALUES (?, ?, NOW())');
            $add->bind_param('ii', $songId, $playlistId);
            $add->execute();
            $add->close();
            $db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Song added to playlist.']);
            exit;
        }

        $remove = $db->prepare('DELETE FROM playlist_musics WHERE id_playlist = ? AND id_music = ?');
        $removed = 0;
        foreach ($songIds as $songId) {
            $remove->bind_param('ii', $playlistId, $songId);
            $remove->execute();
            $removed += $remove->affected_rows;
        }
        $remove->close();
        $db->commit();

        if ($removed === 0) {
            sendSongJsonError(404, 'songs_not_in_playlist', 'None of the selected songs are in this playlist.');
        }
        echo json_encode([
            'status' => 'success',
            'message' => $removed === 1 ? 'Song removed from playlist.' : "$removed songs removed from playlist.",
            'removed_count' => $removed,
        ]);
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
} catch (Throwable $error) {
    error_log('Playlist track mutation error: ' . $error->getMessage());
    sendSongJsonError(500, 'server_error', 'Could not update playlist songs.');
} finally {
    if (isset($db)) {
        $db->close();
    }
}
