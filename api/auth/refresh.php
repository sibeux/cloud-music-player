<?php

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/auth_jwt.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secretKey = $_ENV['JWT_AUTH_SECRET'] ?? null;

if (!$secretKey) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal Server Error: Secret key missing"]);
    exit;
}

header('Content-Type: application/json');

// --- AMBIL TOKEN DARI HEADER BEARER ---
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
$oldRefreshToken = null;

if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $oldRefreshToken = $matches[1];
}

if (!$oldRefreshToken) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Refresh token required"]);
    exit;
}

try {
    $decoded = JWT::decode($oldRefreshToken, new Key($secretKey, 'HS256'));
    $jti = $decoded->jti;
    $userId = $decoded->sub;

    $hashedOldToken = hash('sha256', $oldRefreshToken);
    $stmt = $db->prepare("SELECT user_id FROM refresh_tokens WHERE jti = ? AND token_hash = ? AND is_revoked = 0 LIMIT 1");
    $stmt->bind_param("ss", $jti, $hashedOldToken);
    $stmt->execute();
    $res = $stmt->get_result();
    $tokenRow = $res->fetch_assoc();

    if (!$tokenRow) {
        throw new Exception("Token tidak valid atau sudah digunakan.");
    }

    $userStmt = $db->prepare("SELECT user_id, name, email, role FROM users WHERE user_id = ? LIMIT 1");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception("User tidak ditemukan.");
    }

    // ROTATION: Hapus token lama
    $delStmt = $db->prepare("DELETE FROM refresh_tokens WHERE jti = ?");
    $delStmt->bind_param("s", $jti);
    $delStmt->execute();

    // Generate & Save New Tokens
    $token = helperRefreshMethod($user, $secretKey, $db);

    echo json_encode([
        'status' => 'success',
        'message' => 'Token berhasil diperbarui',
        'access_token' => $token['access_token'],
        'refresh_token' => $token['refresh_token']
    ]);

} catch (Exception $e) {
    // SECURITY: Jika terjadi anomali (misal token reuse), hapus semua sesi user ini
    if (isset($userId)) {
        $delAllStmt = $db->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $delAllStmt->bind_param("i", $userId);
        $delAllStmt->execute();
    }

    error_log("Refresh Error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesi berakhir, silakan login kembali.'
    ]);
}

$db->close();