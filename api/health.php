<?php
require_once __DIR__ . '/cors.php';

echo json_encode([
    'status' => 'success',
    'message' => 'Backend is healthy',
    'time' => date('c'),
]);
