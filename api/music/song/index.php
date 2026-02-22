<?php

require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/get_song_by_album.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;
    $role = isset($user['data']['role']) ? $user['data']['role'] : 'user';

    if (isset($_GET['type'])) {
    switch ($_GET['type']) {
        case 'album':
            getSongByAlbum($db, $_GET['album_id'], $role);
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