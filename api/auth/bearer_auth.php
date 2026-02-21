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
    private $secretKey;

    public function __construct($key) {
        $this->secretKey = $key;
    }

    public function validate() {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            $this->responseError("Unauthorized: Token missing");
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            $this->responseError("Unauthorized: " . $e->getMessage());
        }
    }

    private function responseError($msg) {
        http_response_code(401);
        echo json_encode(["message" => $msg]);
        exit();
    }
}