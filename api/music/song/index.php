<?php

ob_start('ob_gzhandler');
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/get_song_by_album.php';
require_once __DIR__ . '/get_song_by_category.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;
    $role = isset($user['data']['role']) ? $user['data']['role'] : 'user';

    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $uid = isset($_GET['uid']) ? $_GET['uid'] : null;

    if (isset($type)) {
    switch ($type) {
        case 'album':
            getSongByAlbum($db, $uid, $role);
            break;
        case 'category':
            getSongByCategory($db, $uid, $role);
            break;
        default:
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "error" => "invalid_method",
                "message" => "Method tidak valid"
            ]);
            break;
    }
    } else {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "error" => "method_not_provided",
            "message" => "Method tidak disediakan"
        ]);
    }

    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
} finally{
     // Tutup koneksi setelah semua selesai
    if (isset($db)) {
        // Sesuaikan dengan driver: $db->close() untuk MySQLi, $db = null untuk PDO
        $db->close();
    }
}
exit;