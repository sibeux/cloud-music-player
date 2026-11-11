<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/queries/get_song_by_album.php';
require_once __DIR__ . '/queries/get_song_by_category.php';
require_once __DIR__ . '/queries/get_song_by_playlist.php';
require_once __DIR__ . '/queries/get_song_by_recent.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET') {
        header('Allow: GET');
        sendSongJsonError(405, 'method_not_allowed', 'Gunakan method GET.');
    }

    [$userId, $role] = authenticateSongUser($secretKey);

    $type = isset($_GET['type']) ? trim((string) $_GET['type']) : '';
    $uid = isset($_GET['uid']) ? trim((string) $_GET['uid']) : '';
    $search = isset($_GET['search']) ? trim((string) $_GET['search']) : (isset($_GET['q']) ? trim((string) $_GET['q']) : '');
    $sort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : (isset($_GET['sort_by']) ? trim((string) $_GET['sort_by']) : (isset($_GET['order_by']) ? trim((string) $_GET['order_by']) : ''));
    $order = isset($_GET['order']) ? trim((string) $_GET['order']) : (isset($_GET['sort_order']) ? trim((string) $_GET['sort_order']) : (isset($_GET['direction']) ? trim((string) $_GET['direction']) : 'asc'));

    if ($type === '') {
        sendSongJsonError(400, 'type_not_provided', "Parameter 'type' wajib disediakan ('category', 'playlist', atau 'recent').");
    }

    if ($uid === '' && in_array($type, ['recent', 'history'], true)) {
        $uid = (string) $userId;
    }

    if ($uid === '') {
        sendSongJsonError(400, 'uid_not_provided', "Parameter 'uid' wajib disediakan.");
    }

    $isSearchOrSortRequested = ($search !== '' || $sort !== '');

    if ($type === 'album') {
        if ($isSearchOrSortRequested) {
            sendSongJsonError(400, 'unsupported_album_type', "Fitur search dan sort lagu hanya berlaku khusus untuk album bertipe 'category' atau 'playlist'.");
        }
        $songs = getSongsByAlbum($db, (int) $uid, $role);
    } elseif ($type === 'category') {
        $songs = getSongsByCategory($db, $uid, $role, $search, $sort, $order);
    } elseif ($type === 'playlist') {
        if ($userId <= 0) {
            sendSongJsonError(401, 'unauthorized', 'Autentikasi diperlukan untuk mengakses playlist.');
        }
        $songs = getSongsByPlaylist($db, (int) $uid, $userId, $search, $sort, $order);
    } elseif ($type === 'recent' || $type === 'history') {
        if ($userId <= 0) {
            sendSongJsonError(401, 'unauthorized', 'Autentikasi diperlukan untuk mengakses recent music.');
        }
        // Khusus role admin diizinkan melihat history user lain via uid
        $targetUserId = $userId;
        if ($role === 'admin' && (int) $uid > 0) {
            $targetUserId = (int) $uid;
        }
        $songs = getSongsByRecent($db, $targetUserId, $search, $sort, $order);
    } else {
        sendSongJsonError(400, 'invalid_type', "Tipe tidak valid. Tipe yang didukung: 'category', 'playlist', atau 'recent'.");
    }

    echo json_encode([
        'status' => 'success',
        'data' => $songs
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log('Song API error: ' . $e->getMessage());
    sendSongJsonError(500, 'server_error', 'Terjadi kesalahan server: ' . $e->getMessage());
} finally {
    if (isset($db)) {
        $db->close();
    }
}
exit;
