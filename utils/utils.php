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
    echo json_encode($data);
    die();
}