<?php
// Fungsi untuk membuat log manual
function log_message($message) {
    $logFile = 'custom.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// --- Fungsi Helper ---
function sendJsonResponses(array $data, int $responseCode = 200)
{
    http_response_code($responseCode);
    header('Content-Type: application/json');
    // Header anti-cache
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($data);
    die();
}