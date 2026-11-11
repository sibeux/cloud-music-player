<?php

require_once __DIR__ . '/../users/bootstrap.php';
require_once __DIR__ . '/queries/preview_artist_merge.php';
require_once __DIR__ . '/commands/merge_artists.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    requireAdminUser($db, $secretKey);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $sourceArtistId = filter_input(INPUT_GET, 'source_artist_id', FILTER_VALIDATE_INT);
        $targetArtistId = filter_input(INPUT_GET, 'target_artist_id', FILTER_VALIDATE_INT);
        $preview = previewArtistMerge($db, (int) $sourceArtistId, (int) $targetArtistId);

        echo json_encode([
            'status' => 'success',
            'data' => $preview,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            sendAdminJsonError(400, 'invalid_json', 'Kirim body JSON yang valid.');
        }

        $result = mergeArtists(
            $db,
            (int) ($body['source_artist_id'] ?? 0),
            (int) ($body['target_artist_id'] ?? 0),
            $body['confirm_source_name'] ?? null
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Artist berhasil digabungkan.',
            'data' => $result,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        header('Allow: GET, POST');
        sendAdminJsonError(405, 'method_not_allowed', 'Gunakan GET atau POST.');
    }
} catch (ArtistMergeException $error) {
    sendAdminJsonError($error->getHttpStatus(), $error->getErrorCode(), $error->getMessage());
} catch (Throwable $error) {
    error_log('Admin artist merge error: ' . $error->getMessage());
    sendAdminJsonError(500, 'server_error', 'Terjadi kesalahan server. Merge dibatalkan.');
} finally {
    if (isset($db)) {
        $db->close();
    }
}

exit;
