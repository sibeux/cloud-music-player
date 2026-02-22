<?php

require_once __DIR__ . '/../../init.php';

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
            echo json_encode(["error" => "Invalid method"]);
            break;
    }
    } else {
        echo json_encode(["error" => "Method not provided"]);
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