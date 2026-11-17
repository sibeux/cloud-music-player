<?php

require_once __DIR__ . '/../users/bootstrap.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

class ArtistEditException extends RuntimeException
{
    public $httpStatus;
    public $errorCode;

    public function __construct($httpStatus, $errorCode, $message)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
    }
}

function editArtistRecord($db, $artistId, $lock = false)
{
    $stmt = $db->prepare('SELECT id_artist, name FROM cybeat_artists WHERE id_artist = ?' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->bind_param('i', $artistId);
    $stmt->execute();
    $artist = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$artist) throw new ArtistEditException(404, 'artist_not_found', 'Artist not found. Search again.');
    return ['id_artist' => (int) $artist['id_artist'], 'name' => $artist['name']];
}

function editArtistAlbums($db, $artistName, $lock = false)
{
    $stmt = $db->prepare('SELECT uid, name, author FROM albums WHERE LOCATE(?, author) > 0 ORDER BY uid ASC' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->bind_param('s', $artistName);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $albums = [];
    foreach ($rows as $row) {
        $credits = array_map('trim', explode(',', (string) $row['author']));
        if (in_array($artistName, $credits, true)) {
            $albums[] = [
                'id' => (int) $row['uid'],
                'name' => $row['name'],
                'author_before' => $row['author'],
            ];
        } elseif (strpos((string) $row['author'], $artistName) !== false) {
            throw new ArtistEditException(409, 'album_credit_needs_review', 'An album credit contains this artist in another format. Update that album credit before renaming.');
        }
    }
    return $albums;
}

function editArtistToken($artist, $albums)
{
    return hash('sha256', json_encode([$artist, $albums], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function ensureArtistEditTablesTransactional($db)
{
    $result = $db->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('cybeat_artists', 'albums')");
    $engines = [];
    while ($row = $result->fetch_assoc()) $engines[$row['TABLE_NAME']] = strtoupper((string) $row['ENGINE']);
    $result->free();
    if (($engines['cybeat_artists'] ?? null) !== 'INNODB' || ($engines['albums'] ?? null) !== 'INNODB') {
        throw new ArtistEditException(409, 'artist_edit_requires_innodb', 'Artist and album tables must use InnoDB for a safe rename.');
    }
}

try {
    requireAdminUser($db, $secretKey);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET' && $method !== 'PATCH') {
        header('Allow: GET, PATCH');
        throw new ArtistEditException(405, 'method_not_allowed', 'Use GET or PATCH.');
    }
    $input = $method === 'PATCH' ? json_decode(file_get_contents('php://input'), true) : $_GET;
    if (!is_array($input)) throw new ArtistEditException(400, 'invalid_json', 'Send a valid JSON body.');
    $artistId = filter_var($input['artist_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$artistId || $artistId < 1) throw new ArtistEditException(422, 'invalid_artist', 'Select an artist to edit.');

    if ($method === 'GET') {
        $artist = editArtistRecord($db, $artistId);
        $albums = editArtistAlbums($db, $artist['name']);
        echo json_encode(['status' => 'success', 'data' => [
            'artist' => $artist,
            'albums' => $albums,
            'preview_token' => editArtistToken($artist, $albums),
        ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        $newName = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        if ($newName === '' || mb_strlen($newName) > 255 || strpos($newName, ',') !== false) {
            throw new ArtistEditException(422, 'invalid_name', 'Enter a name up to 255 characters without commas.');
        }
        if (!is_string($input['expected_name'] ?? null) || !is_string($input['preview_token'] ?? null)) {
            throw new ArtistEditException(422, 'preview_required', 'Load the artist before saving changes.');
        }
        ensureArtistEditTablesTransactional($db);
        $db->begin_transaction();
        try {
            $artist = editArtistRecord($db, $artistId, true);
            $albums = editArtistAlbums($db, $artist['name'], true);
            if ($artist['name'] !== $input['expected_name'] || !hash_equals(editArtistToken($artist, $albums), $input['preview_token'])) {
                throw new ArtistEditException(409, 'edit_preview_stale', 'Artist or album data changed. Reload the artist before saving.');
            }
            if ($newName === $artist['name']) throw new ArtistEditException(422, 'name_unchanged', 'Enter a different artist name.');

            $stmt = $db->prepare('SELECT id_artist FROM cybeat_artists WHERE LOWER(TRIM(name)) = LOWER(?) AND id_artist <> ? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('si', $newName, $artistId);
            $stmt->execute();
            $duplicate = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($duplicate) throw new ArtistEditException(409, 'name_exists', 'An artist with this name already exists. Use Merge artists for duplicates.');

            $stmt = $db->prepare('UPDATE cybeat_artists SET name = ? WHERE id_artist = ?');
            $stmt->bind_param('si', $newName, $artistId);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare('UPDATE albums SET author = ? WHERE uid = ?');
            $updatedAlbums = [];
            foreach ($albums as $album) {
                $credits = array_map('trim', explode(',', (string) $album['author_before']));
                $updated = array_map(function ($credit) use ($artist, $newName) {
                    return $credit === $artist['name'] ? $newName : $credit;
                }, $credits);
                $authorAfter = implode(', ', $updated);
                $stmt->bind_param('si', $authorAfter, $album['id']);
                $stmt->execute();
                $updatedAlbums[] = ['id' => $album['id'], 'author_after' => $authorAfter];
            }
            $stmt->close();

            $savedArtist = editArtistRecord($db, $artistId);
            if ($savedArtist['name'] !== $newName) throw new RuntimeException('Artist name was not saved completely.');
            $stmt = $db->prepare('SELECT author FROM albums WHERE uid = ?');
            foreach ($updatedAlbums as $album) {
                $stmt->bind_param('i', $album['id']);
                $stmt->execute();
                $saved = $stmt->get_result()->fetch_assoc();
                if (!$saved || $saved['author'] !== $album['author_after']) throw new RuntimeException('Album credit was not saved completely.');
            }
            $stmt->close();

            $db->commit();
            echo json_encode(['status' => 'success', 'data' => [
                'artist' => $savedArtist,
                'album_count' => count($updatedAlbums),
            ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }
} catch (ArtistEditException $error) {
    sendAdminJsonError($error->httpStatus, $error->errorCode, $error->getMessage());
} catch (Throwable $error) {
    error_log('Admin artist edit error: ' . $error->getMessage());
    sendAdminJsonError(500, 'server_error', 'A server error occurred. The rename was canceled.');
} finally {
    if (isset($db)) $db->close();
}

exit;
