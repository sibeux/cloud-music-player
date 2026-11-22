<?php
require_once __DIR__ . '/../../../utils/utils.php';
require_once __DIR__ . '/../../init.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = $user['sub'] ?? 0;
    $role = $user['data']['role'] ?? 'free';

    require_once __DIR__ . '/get_category.php';
    require_once __DIR__ . '/get_playlist.php';
    require_once __DIR__ . '/get_library_albums.php';

    echo json_encode([
        'status' => 'success',
        'data' => [
            'category' => get_category($db, $userId, $role),
            'playlist' => get_playlist($db, $userId),
            'album' => getLibraryAlbums($db, (int) $userId, $role),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
} finally {
    if (isset($db)) $db->close();
}
