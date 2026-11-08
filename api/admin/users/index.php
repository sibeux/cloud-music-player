<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/queries/list_users.php';
require_once __DIR__ . '/commands/change_user_role.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    $adminUserId = requireAdminUser($db, $secretKey);

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        echo json_encode([
            'status' => 'success',
            'data' => listUsers($db)
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif ($method === 'PATCH') {
        $body = json_decode(file_get_contents('php://input'), true);
        $result = changeUserRole(
            $db,
            $adminUserId,
            (int) ($body['user_id'] ?? 0),
            (string) ($body['role'] ?? '')
        );
        echo json_encode([
            'status' => 'success',
            'message' => 'Role user berhasil diperbarui.',
            'data' => $result
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        header('Allow: GET, PATCH');
        sendAdminJsonError(405, 'method_not_allowed', 'Gunakan GET atau PATCH.');
    }
} catch (Throwable $error) {
    error_log('Admin user management error: ' . $error->getMessage());
    sendAdminJsonError(500, 'server_error', 'Terjadi kesalahan server.');
} finally {
    if (isset($db)) {
        $db->close();
    }
}
