<?php
header("Content-Type: application/json");
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/auth_jwt.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$secretKey = $_ENV['JWT_AUTH_SECRET'] ?? null;

if (!$secretKey) {
    http_response_code(500);
    echo json_encode(["status" => "error", "error" => "secret_key_missing", "message" => "Internal Server Error: Secret key missing"]);
    exit;
}

// Ambil input JSON
// php://input adalah read-only stream yang memungkinkan untuk membaca data mentah (raw data) dari body HTTP request,
// tidak peduli apa formatnya (JSON, XML, atau teks biasa).
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    // Cari user di database
    $query = "SELECT user_id, password, name, role, email FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $data->email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    // Verifikasi Password
    if ($user && password_verify($data->password, $user['password'])) {
        
        // Generate & Save New Tokens
        $token = helperRefreshMethod($user, $secretKey, $db);

        echo json_encode([
            "status" => "success",
            "message" => "Login berhasil",
            "access_token" => $token['access_token'],
            "refresh_token" => $token['refresh_token']
        ]);

    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "error" => "credentials_mismatch", "message" => "Email atau Password salah."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "error" => "bad_request", "message" => "Email dan Password harus diisi."]);
}

$db->close();