<?php

require_once __DIR__ . '/../users/bootstrap.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

class ArtistSplitException extends RuntimeException
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

function splitInput($sourceId, $names)
{
    if ($sourceId < 1 || !is_array($names) || count($names) < 2) {
        throw new ArtistSplitException(422, 'invalid_split', 'Select one source artist and at least two destination artists.');
    }
    $names = array_map(function ($name) {
        return is_string($name) ? trim($name) : '';
    }, array_values($names));
    foreach ($names as $name) {
        if ($name === '' || mb_strlen($name) > 255 || strpos($name, ',') !== false) {
            throw new ArtistSplitException(422, 'invalid_target_name', 'Destination artist names must be valid and cannot contain commas.');
        }
    }
    if (count(array_unique(array_map('mb_strtolower', $names))) !== count($names)) {
        throw new ArtistSplitException(422, 'duplicate_targets', 'Each destination artist must be different.');
    }
    return $names;
}

function splitSource($db, $sourceId, $lock = false)
{
    $stmt = $db->prepare('SELECT id_artist, name FROM cybeat_artists WHERE id_artist = ?' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->bind_param('i', $sourceId);
    $stmt->execute();
    $source = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$source) {
        throw new ArtistSplitException(404, 'artist_not_found', 'The source artist was not found.');
    }
    return ['id_artist' => (int) $source['id_artist'], 'name' => $source['name']];
}

function splitTarget($db, $name, $source, $create = false)
{
    if (mb_strtolower($name) === mb_strtolower($source['name'])) {
        throw new ArtistSplitException(422, 'target_is_source', 'Destination artists must differ from the source artist.');
    }
    $stmt = $db->prepare('SELECT id_artist, name FROM cybeat_artists WHERE LOWER(TRIM(name)) = LOWER(?) ORDER BY id_artist ASC' . ($create ? ' FOR UPDATE' : ''));
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    if (count($matches) > 1) {
        throw new ArtistSplitException(409, 'ambiguous_target', 'Multiple artists match a destination name. Merge the duplicates first.');
    }
    if ($matches) {
        return ['id_artist' => (int) $matches[0]['id_artist'], 'name' => $matches[0]['name'], 'is_new' => false];
    }
    if (!$create) {
        return ['id_artist' => null, 'name' => $name, 'is_new' => true];
    }
    $stmt = $db->prepare('INSERT INTO cybeat_artists (name) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return ['id_artist' => (int) $id, 'name' => $name, 'is_new' => true];
}

function splitAlbumCredits($db, $sourceName, $targetNames, $lock = false)
{
    $stmt = $db->prepare('SELECT uid, name, author FROM albums WHERE LOCATE(?, author) > 0' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->bind_param('s', $sourceName);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $albums = [];
    foreach ($rows as $row) {
        $credits = array_map('trim', explode(',', (string) $row['author']));
        $changed = false;
        $updated = [];
        foreach ($credits as $credit) {
            if ($credit === $sourceName) {
                array_push($updated, ...$targetNames);
                $changed = true;
            } else {
                $updated[] = $credit;
            }
        }
        if ($changed) {
            $albums[] = [
                'id' => (int) $row['uid'],
                'name' => $row['name'],
                'author_before' => $row['author'],
                'author_after' => implode(', ', $updated),
            ];
        } elseif (strpos((string) $row['author'], $sourceName) !== false) {
            throw new ArtistSplitException(409, 'album_credit_needs_review', 'An album credit contains the combined name in another format. Update that album credit before splitting.');
        }
    }
    return $albums;
}

function ensureSplitTablesTransactional($db)
{
    $result = $db->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('cybeat_artists', 'cybeat_music_artists', 'albums')");
    $engines = [];
    while ($row = $result->fetch_assoc()) $engines[$row['TABLE_NAME']] = strtoupper((string) $row['ENGINE']);
    $result->free();
    foreach (['cybeat_artists', 'cybeat_music_artists', 'albums'] as $table) {
        if (($engines[$table] ?? null) !== 'INNODB') {
            throw new ArtistSplitException(409, 'split_requires_innodb', 'Artist, song link, and album tables must use InnoDB for a safe split.');
        }
    }
}

try {
    requireAdminUser($db, $secretKey);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET' && $method !== 'POST') {
        header('Allow: GET, POST');
        throw new ArtistSplitException(405, 'method_not_allowed', 'Use GET or POST.');
    }
    $input = $method === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;
    if (!is_array($input)) throw new ArtistSplitException(400, 'invalid_json', 'Send a valid JSON body.');
    $sourceId = filter_var($input['source_artist_id'] ?? null, FILTER_VALIDATE_INT);
    $targetNames = splitInput((int) $sourceId, $input['target_names'] ?? null);
    $confirmedName = $input['confirm_source_name'] ?? null;

    if ($method === 'POST') {
        ensureSplitTablesTransactional($db);
        $db->begin_transaction();
    }
    try {
        $source = splitSource($db, (int) $sourceId, $method === 'POST');
        if ($method === 'POST' && $confirmedName !== $source['name']) {
            throw new ArtistSplitException(409, 'source_name_changed', 'Type the source artist name exactly as shown in the preview.');
        }
        $targets = array_map(function ($name) use ($db, $source, $method) {
            return splitTarget($db, $name, $source, $method === 'POST');
        }, $targetNames);
        $existingTargetIds = array_filter(array_column($targets, 'id_artist'), function ($id) { return $id !== null; });
        if (count(array_unique($existingTargetIds)) !== count($existingTargetIds)) {
            throw new ArtistSplitException(422, 'duplicate_targets', 'Select different destination artists.');
        }

        $stmt = $db->prepare('SELECT id_music FROM cybeat_music_artists WHERE id_artist = ? ORDER BY id_music ASC' . ($method === 'POST' ? ' FOR UPDATE' : ''));
        $stmt->bind_param('i', $sourceId);
        $stmt->execute();
        $musicIds = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id_music'));
        $stmt->close();
        if (!$musicIds) throw new ArtistSplitException(422, 'no_source_songs', 'The source artist has no songs to split.');

        $albums = splitAlbumCredits($db, $source['name'], array_column($targets, 'name'), $method === 'POST');
        $previewToken = hash('sha256', json_encode([$source, $targetNames, $musicIds, $albums], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ($method === 'POST' && (
            !isset($input['expected_song_count'], $input['expected_album_count'])
            || (int) $input['expected_song_count'] !== count($musicIds)
            || (int) $input['expected_album_count'] !== count($albums)
            || !is_string($input['preview_token'] ?? null)
            || !hash_equals($previewToken, $input['preview_token'])
        )) {
            throw new ArtistSplitException(409, 'split_preview_stale', 'Song, artist, or album data changed. Refresh the preview before saving.');
        }
        if ($method === 'GET') {
            $stmt = $db->prepare('SELECT m.id_music, m.title FROM cybeat_music_artists ma JOIN musics m ON m.id_music = ma.id_music WHERE ma.id_artist = ? ORDER BY m.id_music ASC LIMIT 30');
            $stmt->bind_param('i', $sourceId);
            $stmt->execute();
            $songs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(['status' => 'success', 'data' => [
                'source' => $source,
                'targets' => $targets,
                'song_count' => count($musicIds),
                'song_sample' => $songs,
                'albums' => $albums,
                'preview_token' => $previewToken,
            ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            foreach ($targets as $target) {
                $stmt = $db->prepare('INSERT IGNORE INTO cybeat_music_artists (id_music, id_artist) SELECT id_music, ? FROM cybeat_music_artists WHERE id_artist = ?');
                $stmt->bind_param('ii', $target['id_artist'], $sourceId);
                $stmt->execute();
                $stmt->close();
                $stmt = $db->prepare('SELECT COUNT(*) AS linked_count FROM cybeat_music_artists source JOIN cybeat_music_artists target ON target.id_music = source.id_music AND target.id_artist = ? WHERE source.id_artist = ?');
                $stmt->bind_param('ii', $target['id_artist'], $sourceId);
                $stmt->execute();
                $linkedCount = (int) $stmt->get_result()->fetch_assoc()['linked_count'];
                $stmt->close();
                if ($linkedCount !== count($musicIds)) throw new RuntimeException('Target song links were not complete.');
            }
            $stmt = $db->prepare('UPDATE albums SET author = ? WHERE uid = ?');
            foreach ($albums as $album) {
                $stmt->bind_param('si', $album['author_after'], $album['id']);
                $stmt->execute();
            }
            $stmt->close();
            $stmt = $db->prepare('SELECT author FROM albums WHERE uid = ?');
            foreach ($albums as $album) {
                $stmt->bind_param('i', $album['id']);
                $stmt->execute();
                $saved = $stmt->get_result()->fetch_assoc();
                if (!$saved || $saved['author'] !== $album['author_after']) {
                    throw new RuntimeException('Album credit was not saved completely.');
                }
            }
            $stmt->close();
            $stmt = $db->prepare('DELETE FROM cybeat_music_artists WHERE id_artist = ?');
            $stmt->bind_param('i', $sourceId);
            $stmt->execute();
            if ($stmt->affected_rows !== count($musicIds)) throw new RuntimeException('Source song links changed during split.');
            $stmt->close();
            $stmt = $db->prepare('DELETE FROM cybeat_artists WHERE id_artist = ?');
            $stmt->bind_param('i', $sourceId);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) throw new RuntimeException('Source artist was not deleted.');
            $stmt->close();
            $db->commit();
            echo json_encode(['status' => 'success', 'data' => [
                'source' => $source, 'targets' => $targets,
                'song_count' => count($musicIds), 'album_count' => count($albums),
            ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    } catch (Throwable $error) {
        if ($method === 'POST') $db->rollback();
        throw $error;
    }
} catch (ArtistSplitException $error) {
    sendAdminJsonError($error->httpStatus, $error->errorCode, $error->getMessage());
} catch (Throwable $error) {
    error_log('Admin artist split error: ' . $error->getMessage());
    sendAdminJsonError(500, 'server_error', 'A server error occurred. The split was canceled.');
} finally {
    if (isset($db)) $db->close();
}

exit;
