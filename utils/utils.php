<?php
// Fungsi untuk membuat log manual
function log_message($message)
{
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

function urlFormatter($url)
{
    if (stripos($url, 'drive.google.com') !== false) {
        return [
            'type' => 'gdrive',
            'url' => $url
        ];
    } else if (stripos($url, 'cdncloudflare/') !== false) {
        $path = str_replace("cdncloudflare", '', $url);
        $path = str_replace('%20', ' ', $path);
        return [
            'type' => 'cdncloudflare',
            'url' => $path
        ];
    } else if (stripos($url, 'github') !== false && stripos($url, 'raw=true') !== false) {
        $githubUrl = githubUrlFormatter($url);
        return [
            'type' => 'github',
            'url' => $githubUrl
        ];
    } else {
        return [
            'type' => 'other',
            'url' => $url
        ];
    }
}

function githubUrlFormatter($url)
{
    $githubUrl = str_replace("https://github.com/", "https://raw.githubusercontent.com/", $url);
    $githubUrl = str_replace("/blob/", "/refs/heads/", $githubUrl);
    $githubUrl = explode("?", $githubUrl)[0];
    return $githubUrl;
}