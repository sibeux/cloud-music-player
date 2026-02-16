<?php

// Pakai __DIR__ karena php saat di-include,
// file yang di-include jadi otomatis "berpindah"
// ke file yang meng-include.
require_once __DIR__ . '/../../db.php';

$conn = $db;

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');