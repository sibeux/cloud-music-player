<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/pin.php';
require_once __DIR__ . '/unpin.php';

try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? $user['sub'] : 0;
    $role = isset($user['data']['role']) ? $user['data']['role'] : 'user';

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $albumId = isset($data['albumId']) ? $data['albumId'] : 0;
        $albumType = isset($data['albumType']) ? strtolower($data['albumType']) : 'album';
        pin($db, $userId, $albumId, $albumType);
    } else if ($method === 'DELETE') {
        $albumId = isset($_GET['albumId']) ? $_GET['albumId'] : 0;
        $albumType = isset($_GET['albumType']) ? strtolower($_GET['albumType']) : 'album';
        unpin($db, $userId, $albumId, $albumType);
    } else {
        http_response_code(405);
        echo json_encode([
            "status" => "error",
            "message" => "Method not allowed"
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error",
        "error" => $e->getMessage()
    ]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
exit;