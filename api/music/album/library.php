<?php
require_once __DIR__ . '/../../init.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = (int) ($user['sub'] ?? 0);
    if ($userId <= 0) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Sign in to manage your library.']);
        exit;
    }
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['POST', 'DELETE'], true)) {
        header('Allow: POST, DELETE');
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    if (!is_array($input)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request body']);
        exit;
    }
    $albumId = (int) ($input['album_id'] ?? $_GET['album_id'] ?? 0);
    $role = $user['data']['role'] ?? 'free';
    $privacy = in_array($role, ['premium', 'admin'], true) ? '1=1' : 'is_private = 0';
    if ($method === 'POST') {
        $stmt = $db->prepare("SELECT uid FROM albums WHERE uid = ? AND $privacy LIMIT 1");
        $stmt->bind_param('i', $albumId);
        $stmt->execute();
        $available = $stmt->get_result()->fetch_row();
        $stmt->close();
        if (!$available) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Album not found']);
            exit;
        }
        $stmt = $db->prepare('INSERT IGNORE INTO album_library (user_id, album_id) VALUES (?, ?)');
    } else {
        $stmt = $db->prepare('DELETE FROM album_library WHERE user_id = ? AND album_id = ?');
    }
    $stmt->bind_param('ii', $userId, $albumId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not update library']);
} finally {
    if (isset($db)) $db->close();
}
