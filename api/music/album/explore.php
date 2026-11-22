<?php

ob_start('ob_gzhandler');
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../../../utils/utils.php';
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/get_explore_album.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method tidak diizinkan. Gunakan GET.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? (int) $user['sub'] : 0;
    $role = isset($user['data']['role']) ? $user['data']['role'] : 'free';

    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    if ($limit < 1) {
        $limit = 10;
    } elseif ($limit > 50) {
        $limit = 50;
    }

    $search = is_string($_GET['search'] ?? null) ? trim($_GET['search']) : '';

    $cursor = (isset($_GET['cursor']) && is_numeric($_GET['cursor'])) ? (int) $_GET['cursor'] : null;

    // Jika category_id disediakan, ambil spesifik untuk kategori tersebut (dengan keyset pagination)
    if (isset($_GET['category_id'])) {
        $categoryId = (int) $_GET['category_id'];

        if (!array_key_exists($categoryId, ALLOWED_EXPLORE_CATEGORIES)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Category ID {$categoryId} tidak valid untuk explore album. Kategori yang tersedia: 1 (IndoPride), 2 (日本の歌), 3 (Jowo Mletre), 4 (Worldwide), 5 (Instrumental).",
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $categoryInfo = getCategoryExploreInfo($db, $categoryId, $role);
        $resultData = getExploreAlbumsByCategory($db, $categoryId, $role, $cursor, $limit, $search);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'category' => $categoryInfo,
                'albums' => $resultData['albums'],
                'next_cursor' => $resultData['next_cursor'],
                'has_more' => $resultData['has_more'],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Jika category_id tidak disediakan, ambil 5 section sekaligus (masing-masing limit album pertama)
    $sections = getAllExploreCategories($db, $role, $limit);

    echo json_encode([
        'status' => 'success',
        'data' => $sections,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} finally {
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}
exit;
