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

class BearerAuth
{
    private $secretKey;

    public function __construct($key)
    {
        $this->secretKey = $key;
    }

    public function validate($strict = true)
    {
        $headers = getallheaders();

        // Cek Header
        if (!isset($headers['Authorization'])) {
            if ($strict) {
                $this->responseError("Unauthorized: Token missing");
            }
            return null; // Guest user
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        // Cek Token
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded; // Langsung cast ke array lebih simpel
        } catch (Exception $e) {
            if ($strict) {
                $this->responseError("Unauthorized: " . $e->getMessage());
            }
            return null; // Token expired/salah tapi tetap lanjut (untuk free user)
        }
    }

    private function responseError($msg)
    {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => $msg
        ]);
        exit();
    }
}