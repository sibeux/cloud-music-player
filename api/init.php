<?php
// Header CORS (Wajib agar Flutter bisa akses)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Tangani Preflight Request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load Composer & Class
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/auth/bearer_auth.php';