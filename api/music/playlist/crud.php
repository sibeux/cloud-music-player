<?php
require_once __DIR__ . '/../../init.php';

header('Content-Type: application/json; charset=utf-8');

function playlistResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = (int) ($user['sub'] ?? 0);

    if ($userId <= 0) {
        playlistResponse(401, ['status' => 'error', 'message' => 'Authentication required']);
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $input = [];
    if (in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!is_array($input)) {
            playlistResponse(400, ['status' => 'error', 'message' => 'Invalid JSON body']);
        }
    }

    if ($method === 'GET') {
        $stmt = $db->prepare('SELECT playlist_id AS id, title, cover, created_at FROM playlists WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        playlistResponse(200, ['status' => 'success', 'data' => $items]);
    }

    if ($method === 'POST') {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            playlistResponse(422, ['status' => 'error', 'message' => 'Playlist title is required and must be at most 255 characters']);
        }

        $stmt = $db->prepare('INSERT INTO playlists (title, user_id, created_at) VALUES (?, ?, NOW())');
        $stmt->bind_param('si', $title, $userId);
        if (!$stmt->execute()) {
            throw new RuntimeException('Could not create playlist');
        }
        $playlistId = (int) $db->insert_id;
        $stmt->close();

        playlistResponse(201, [
            'status' => 'success',
            'message' => 'Playlist created',
            'data' => ['id' => $playlistId, 'title' => $title],
        ]);
    }

    $playlistId = filter_var($input['id'] ?? ($_GET['id'] ?? null), FILTER_VALIDATE_INT);
    if (!$playlistId || $playlistId <= 0) {
        playlistResponse(422, ['status' => 'error', 'message' => 'A valid playlist id is required']);
    }

    if ($method === 'PATCH' || $method === 'PUT') {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            playlistResponse(422, ['status' => 'error', 'message' => 'Playlist title is required and must be at most 255 characters']);
        }

        $stmt = $db->prepare('UPDATE playlists SET title = ? WHERE playlist_id = ? AND user_id = ?');
        $stmt->bind_param('sii', $title, $playlistId, $userId);
        $stmt->execute();
        $found = $stmt->affected_rows > 0;
        $stmt->close();
        if (!$found) {
            $check = $db->prepare('SELECT 1 FROM playlists WHERE playlist_id = ? AND user_id = ? LIMIT 1');
            $check->bind_param('ii', $playlistId, $userId);
            $check->execute();
            $found = (bool) $check->get_result()->fetch_row();
            $check->close();
            if (!$found) {
                playlistResponse(404, ['status' => 'error', 'message' => 'Playlist not found']);
            }
        }

        playlistResponse(200, ['status' => 'success', 'message' => 'Playlist updated', 'data' => ['id' => $playlistId, 'title' => $title]]);
    }

    if ($method === 'DELETE') {
        $db->begin_transaction();
        try {
            $check = $db->prepare('SELECT playlist_id FROM playlists WHERE playlist_id = ? AND user_id = ? FOR UPDATE');
            $check->bind_param('ii', $playlistId, $userId);
            $check->execute();
            $owned = (bool) $check->get_result()->fetch_row();
            $check->close();
            if (!$owned) {
                $db->rollback();
                playlistResponse(404, ['status' => 'error', 'message' => 'Playlist not found']);
            }

            $songs = $db->prepare('DELETE FROM playlist_musics WHERE id_playlist = ?');
            $songs->bind_param('i', $playlistId);
            $songs->execute();
            $songs->close();

            $pins = $db->prepare("DELETE FROM album_pins WHERE pinnable_album_id = ? AND pinnable_album_type = 'playlist'");
            $pins->bind_param('i', $playlistId);
            $pins->execute();
            $pins->close();

            $delete = $db->prepare('DELETE FROM playlists WHERE playlist_id = ? AND user_id = ?');
            $delete->bind_param('ii', $playlistId, $userId);
            $delete->execute();
            $delete->close();
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }

        playlistResponse(200, ['status' => 'success', 'message' => 'Playlist deleted']);
    }

    header('Allow: GET, POST, PATCH, PUT, DELETE');
    playlistResponse(405, ['status' => 'error', 'message' => 'Method not allowed']);
} catch (Throwable $error) {
    error_log('Playlist CRUD error: ' . $error->getMessage());
    playlistResponse(500, ['status' => 'error', 'message' => 'Could not process playlist request']);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
