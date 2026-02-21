<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secretKey = $_ENV['JWT_AUTH_SECRET'] ?? null;

if (!$secretKey) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal Server Error: Secret key missing"]);
    exit;
}

class BearerAuth {
    private static $secretKey;

    // Fungsi Validasi (Dipakai di setiap request API)
    public static function validate() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            self::responseError("Unauthorized: Token missing");
        }

        // Ambil token dari format "Bearer <token>"
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // Verifikasi Signature dan Expiration
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return (array) $decoded; // Balikkan data user (id, role, dll)
        } catch (Exception $e) {
            self::responseError("Unauthorized: " . $e->getMessage());
        }
    }

    private static function responseError($msg) {
        http_response_code(401);
        echo json_encode(["message" => $msg]);
        // Langsung hentikan proses hit API.
        exit();
    }
}