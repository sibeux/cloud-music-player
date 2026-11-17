<?php

require_once __DIR__ . '/../users/bootstrap.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    requireAdminUser($db, $secretKey);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        if (array_key_exists('album_id', $_GET)) {
            $albumId = filter_input(INPUT_GET, 'album_id', FILTER_VALIDATE_INT);
            if (!$albumId || $albumId < 1) {
                sendAdminJsonError(400, 'invalid_album_id', 'The album ID is invalid.');
            }
            $stmt = $db->prepare('SELECT uid AS id, name, author, image AS cover, have_disc, is_private FROM albums WHERE uid = ? LIMIT 1');
            $stmt->bind_param('i', $albumId);
            $stmt->execute();
            $album = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$album) {
                sendAdminJsonError(404, 'album_not_found', 'Album not found.');
            }
            $album['id'] = (int) $album['id'];
            $album['have_disc'] = (int) $album['have_disc'];
            $album['is_private'] = (int) $album['is_private'];
            echo json_encode(['status' => 'success', 'data' => ['album' => $album]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $albums = [];
        $result = $db->query("SELECT uid AS id, name, author, image AS cover, have_disc, is_private FROM albums ORDER BY name ASC");
        while ($row = $result->fetch_assoc()) {
            $albums[] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'author' => $row['author'],
                'cover' => $row['cover'],
                'have_disc' => (int) $row['have_disc'],
                'is_private' => (int) $row['is_private'],
            ];
        }

        $categories = [];
        $result = $db->query('SELECT category_id AS id, name, cover FROM categories ORDER BY name ASC');
        while ($row = $result->fetch_assoc()) {
            $categories[(int) $row['id']] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'cover' => $row['cover'],
                'album_ids' => [],
            ];
        }

        $result = $db->query('SELECT category_id, album_id FROM category_albums ORDER BY category_id, album_id');
        while ($row = $result->fetch_assoc()) {
            $categoryId = (int) $row['category_id'];
            if (isset($categories[$categoryId])) {
                $categories[$categoryId]['album_ids'][] = (int) $row['album_id'];
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => ['albums' => $albums, 'categories' => array_values($categories)],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif ($method === 'PATCH') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            sendAdminJsonError(400, 'invalid_body', 'The submitted data is invalid.');
        }

        $type = (string) ($body['type'] ?? '');
        $id = (int) ($body['id'] ?? 0);
        $name = trim((string) ($body['name'] ?? ''));
        $cover = trim((string) ($body['cover'] ?? ''));
        if ($id < 1 || $name === '' || mb_strlen($name) > 255 || mb_strlen($cover) > 2048) {
            sendAdminJsonError(422, 'invalid_catalog_data', 'A name is required and the submitted data must be valid.');
        }

        if ($type === 'album') {
            $author = trim((string) ($body['author'] ?? ''));
            $haveDisc = (int) !empty($body['have_disc']);
            $isPrivate = (int) !empty($body['is_private']);
            $stmt = $db->prepare('UPDATE albums SET name = ?, author = ?, image = ?, have_disc = ?, is_private = ? WHERE uid = ?');
            $stmt->bind_param('sssiii', $name, $author, $cover, $haveDisc, $isPrivate, $id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected === 0) {
                $check = $db->prepare('SELECT uid FROM albums WHERE uid = ? LIMIT 1');
                $check->bind_param('i', $id);
                $check->execute();
                $exists = (bool) $check->get_result()->fetch_assoc();
                $check->close();
                if (!$exists) sendAdminJsonError(404, 'album_not_found', 'Album not found.');
            }
        } elseif ($type === 'category') {
            $albumIds = $body['album_ids'] ?? [];
            if (!is_array($albumIds)) {
                sendAdminJsonError(422, 'invalid_album_ids', 'The category album list is invalid.');
            }
            $albumIds = array_values(array_unique(array_filter(array_map('intval', $albumIds), fn ($value) => $value > 0)));

            $db->begin_transaction();
            try {
                $stmt = $db->prepare('UPDATE categories SET name = ?, cover = ? WHERE category_id = ?');
                $stmt->bind_param('ssi', $name, $cover, $id);
                $stmt->execute();
                $stmt->close();

                $stmt = $db->prepare('SELECT category_id FROM categories WHERE category_id = ? LIMIT 1');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $exists = (bool) $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$exists) {
                    $db->rollback();
                    sendAdminJsonError(404, 'category_not_found', 'Category not found.');
                }

                if ($albumIds) {
                    $placeholders = implode(',', array_fill(0, count($albumIds), '?'));
                    $stmt = $db->prepare("SELECT uid FROM albums WHERE uid IN ($placeholders)");
                    $types = str_repeat('i', count($albumIds));
                    $stmt->bind_param($types, ...$albumIds);
                    $stmt->execute();
                    $validIds = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'uid'));
                    $stmt->close();
                    if (count($validIds) !== count($albumIds)) {
                        $db->rollback();
                        sendAdminJsonError(422, 'album_not_found', 'One or more albums could not be found.');
                    }
                }

                $stmt = $db->prepare('DELETE FROM category_albums WHERE category_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                $stmt = $db->prepare('INSERT INTO category_albums (category_id, album_id) VALUES (?, ?)');
                foreach ($albumIds as $albumId) {
                    $stmt->bind_param('ii', $id, $albumId);
                    $stmt->execute();
                }
                $stmt->close();
                $db->commit();
            } catch (Throwable $error) {
                $db->rollback();
                throw $error;
            }
        } else {
            sendAdminJsonError(422, 'invalid_catalog_type', 'The catalog type is invalid.');
        }

        echo json_encode(['status' => 'success', 'message' => 'Catalog changes saved.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        header('Allow: GET, PATCH');
        sendAdminJsonError(405, 'method_not_allowed', 'Use GET or PATCH.');
    }
} catch (Throwable $error) {
    error_log('Admin catalog management error: ' . $error->getMessage());
    sendAdminJsonError(500, 'server_error', 'An internal server error occurred.');
} finally {
    if (isset($db)) $db->close();
}
