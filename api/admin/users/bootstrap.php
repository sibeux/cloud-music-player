<?php

require_once __DIR__ . '/../../init.php';

function requireAdminUser($db, $secretKey)
{
    $auth = new BearerAuth($secretKey);
    $token = $auth->validate(true);
    $userId = (int) ($token['sub'] ?? 0);

    // Read the current role from the database so a stale JWT cannot keep admin access.
    $stmt = $db->prepare('SELECT role FROM users WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'error' => 'admin_required',
            'message' => 'Akses hanya untuk admin.'
        ]);
        exit;
    }

    return $userId;
}

function sendAdminJsonError($status, $error, $message)
{
    http_response_code($status);
    echo json_encode([
        'status' => 'error',
        'error' => $error,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
