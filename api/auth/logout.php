<?php
header("Content-Type: application/json");
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../database/db.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secretKey = $_ENV['JWT_AUTH_SECRET'] ?? null;

if (!$secretKey) {
    http_response_code(500);
    echo json_encode(["status" => "error", "error" => "secret_key_missing", "message" => "Internal Server Error: Secret key missing"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$plainRefreshToken = $data->refresh_token ?? null;

if ($plainRefreshToken) {
    try {
        // Decode token untuk mendapatkan JTI
        // Kita gunakan key yang sama dengan saat encode
        $decoded = JWT::decode($plainRefreshToken, new Key($secretKey, 'HS256'));
        $jti = $decoded->jti;

        if ($jti) {
            // Hapus dari database berdasarkan JTI
            $query = "DELETE FROM refresh_tokens WHERE jti = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("s", $jti);
            $stmt->execute();

            echo json_encode(["status" => "success", "message" => "Logout berhasil, session dihapus."]);
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "error" => "bad_request", "message" => "JTI tidak ditemukan"]);
        }
    } catch (Exception $e) {
        // Jika token sudah expired atau tidak valid, anggap saja sudah logout
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Token tidak valid", "error" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "error" => "bad_request", "message" => "Refresh token tidak ditemukan"]);
}

$db->close();
