<?php
// Fungsi untuk membuat log manual
function log_message($message)
{
    $logFile = 'custom.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// --- Fungsi Helper ---
function sendJsonResponse(array $data, int $responseCode = 200): void
{
    http_response_code($responseCode);

    header('Content-Type: application/json; charset=utf-8');

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    exit;
}

function outputJson(array $data, int $status = 200): void
{
    http_response_code($status);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
}

function finishResponse(): void
{
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        @ob_end_flush();
        @flush();
    }
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

/**
 * Centralized helper for generating API URLs.
 * Uses APP_URL and API_BASE_PATH from environment configuration.
 *
 * @param string $path The path relative to the API base, e.g., "stream-gdrive.php?music_id=123"
 * @return string The absolute public URL.
 */
function getApiUrl(string $path = ''): string
{
    $appUrl = isset($_ENV['APP_URL']) ? rtrim($_ENV['APP_URL'], '/') : 'http://localhost';
    $apiBasePath = isset($_ENV['API_BASE_PATH']) ? '/' . trim($_ENV['API_BASE_PATH'], '/') : '/api';
    
    // Normalize path to avoid double slashes
    $cleanPath = ltrim($path, '/');
    
    if (empty($cleanPath)) {
        return $appUrl . $apiBasePath;
    }
    
    return $appUrl . $apiBasePath . '/' . $cleanPath;
}

function coverUrlFormatter($path): string
{
    return getApiUrl('music/stream?file_type=image&cover_url=' . rawurlencode($path));
}