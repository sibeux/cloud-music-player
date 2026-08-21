<?php
// CATCH UNCAUGHT EXCEPTIONS AND ERRORS
// Returns 500 HTTP code, JSON payload with error message and trace.

function api_global_exception_handler($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_global_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

function api_global_fatal_handler() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $error['message'],
            'trace' => 'Fatal error - no trace available',
            'file' => $error['file'],
            'line' => $error['line']
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

set_exception_handler('api_global_exception_handler');
set_error_handler('api_global_error_handler');
register_shutdown_function('api_global_fatal_handler');
