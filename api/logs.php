<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../utils/Serilog.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

// Support JSON body actions for POST/DELETE
if ($method === 'POST') {
    $raw = @file_get_contents('php://input');
    if (!empty($raw)) {
        $body = json_decode($raw, true);
        if (isset($body['action'])) {
            $action = $body['action'];
        }
    }
}

// ─── 1. SIMULATE ERROR ACTION (For testing / demonstration) ───────────────────
if ($action === 'simulate_error') {
    function simulateCatalogRepository(int $albumId) {
        simulateDatabaseExecution("SELECT * FROM albums WHERE uid = {$albumId}");
    }

    function simulateDatabaseExecution(string $sql) {
        throw new RuntimeException(
            "Database Query Failure on [{$sql}]: Connection lost to database server at 127.0.0.1:3306 (Error 2013)"
        );
    }

    try {
        simulateCatalogRepository(999);
    } catch (Throwable $e) {
        Serilog::error(
            'CatalogService',
            'Failed to execute catalog database query: ' . $e->getMessage(),
            ['sql' => "SELECT * FROM albums WHERE uid = 999", 'albumId' => 999],
            $e
        );

        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'error' => 'database_error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// ─── 2. CLEAR LOGS ACTION ───────────────────────────────────────────────────
if ($method === 'DELETE' || $action === 'clear') {
    $success = Serilog::clearLogs();
    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Backend server logs cleared successfully' : 'Failed to clear backend logs',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── 3. GET / FETCH LOGS ────────────────────────────────────────────────────
$level = isset($_GET['level']) ? trim($_GET['level']) : 'ALL';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 500;
$sinceId = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;

$logs = Serilog::getLogs([
    'level' => $level,
    'search' => $search,
    'limit' => $limit,
    'since_id' => $sinceId,
]);

echo json_encode([
    'status' => 'success',
    'total' => count($logs),
    'data' => $logs,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
