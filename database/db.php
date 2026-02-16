<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$username = $_ENV['USERNAME'] ?? null;
$password = $_ENV['PASSWORD'] ?? null;
$database = $_ENV['CYBEAT_DATABASE'] ?? null;

if (!$username) {
    die("Error: env USERNAME not found");
}
if (!$password) {
    die("Error: env PASSWORD not found");
}
if (!$database) {
    die("Error: env CYBEAT_DATABASE not found");
}

// Yang perlu diganti saat hosting baru: username, password, dan db.

define('HOST', 'localhost');
define('SIBEUX', $username); // Ganti dengan username database hosting
define('pass', $password); // Ganti dengan password database hosting
define('DB', $database); // Ganti dengan nama database hosting

// define('HOST', 'localhost');
// define('SIBEUX', 'root');
// define('pass', '');
// define('DB', 'sibk1922_cloud_music');

$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

$db->set_charset('utf8mb4');