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

    $albumId = isset($_GET['albumId']) ? (int) $_GET['albumId'] : 0;
    $albumType = isset($_GET['albumType']) ? strtolower(trim($_GET['albumType'])) : 'album';
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (is_array($data)) {
            $albumId = (int) ($data['albumId'] ?? $albumId);
            $albumType = strtolower(trim((string) ($data['albumType'] ?? $albumType)));
        }
    }
    if (!in_array($albumType, ['album', 'playlist', 'category'], true) || $albumId <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Invalid pinnable item.']);
        exit;
    }

    if ($method === 'GET') {
        $stmt = $db->prepare('SELECT 1 FROM album_pins WHERE user_id = ? AND pinnable_album_id = ? AND pinnable_album_type = ? LIMIT 1');
        $stmt->bind_param('iis', $userId, $albumId, $albumType);
        $stmt->execute();
        $pinned = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        echo json_encode(['status' => 'success', 'data' => ['pinned' => $pinned]]);
        exit;
    }

    if ($method === 'POST') {
        pin($db, $userId, $albumId, $albumType);
    } else if ($method === 'DELETE') {
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
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
exit;
