<?php
// Router for PHP built-in server (local development)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // Serve file directly
}

if (file_exists($file . '.php')) {
    require $file . '.php';
    return true;
}

if (rtrim($uri, '/') === '/api/logs') {
    require __DIR__ . '/api/logs.php';
    return true;
}

// Fallback to index if available
if (file_exists(__DIR__ . '/public/index.php')) {
    require __DIR__ . '/public/index.php';
    return true;
}

return false;
