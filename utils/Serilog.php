<?php

/**
 * Serilog-like Structured Request & Application Logger for PHP.
 *
 * Implements structured, leveled logging (DBG, INF, WRN, ERR, FTL)
 * with automatic HTTP request tracking, timing, error stacktraces,
 * and JSON Lines persistence compatible with CyBeat frontend.
 */
class Serilog
{
    private static ?float $startTime = null;
    private static string $method = 'GET';
    private static string $uri = '/';
    private static array $query = [];
    private static ?array $maskedBody = null;
    private static bool $isLoggingStarted = false;
    private static bool $isLogged = false;
    private static ?Throwable $capturedException = null;
    private static ?array $capturedFatal = null;
    private static string $logFile = '';

    /**
     * Initialize log storage path.
     */
    public static function getLogFilePath(): string
    {
        if (!empty(self::$logFile)) {
            return self::$logFile;
        }

        $rootDir = dirname(__DIR__);
        $logDir = $rootDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        // Protect log files from direct HTTP access if served via Apache
        $htaccess = $logDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Require all denied\n");
        }

        self::$logFile = $logDir . DIRECTORY_SEPARATOR . 'requests.jsonl';
        return self::$logFile;
    }

    /**
     * Start automatic request logging for the current HTTP request.
     */
    public static function startRequestLogging(): void
    {
        if (self::$isLoggingStarted) {
            return;
        }
        self::$isLoggingStarted = true;

        self::$startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        self::$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        self::$uri = $_SERVER['REQUEST_URI'] ?? '/';
        self::$query = $_GET ?? [];

        // Pre-parse and mask request body for JSON endpoints
        if (self::$method !== 'GET' && self::$method !== 'OPTIONS' && self::$method !== 'HEAD') {
            $rawInput = @file_get_contents('php://input');
            if (!empty($rawInput)) {
                $decoded = json_decode($rawInput, true);
                if (is_array($decoded)) {
                    self::$maskedBody = self::maskSensitiveFields($decoded);
                } else if (strlen($rawInput) < 2000) {
                    self::$maskedBody = ['raw' => $rawInput];
                }
            }
        }

        // Capture output buffer to extract error details if script emits JSON error
        if (ob_get_level() === 0) {
            ob_start();
        }

        // Register shutdown hook
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Record an uncaught exception for detailed logging at shutdown.
     */
    public static function recordException(Throwable $exception): void
    {
        self::$capturedException = $exception;
    }

    /**
     * Record a PHP fatal error for logging at shutdown.
     */
    public static function recordFatalError(array $error): void
    {
        self::$capturedFatal = $error;
    }

    /**
     * Shutdown hook executed at the end of the request lifecycle.
     */
    public static function handleShutdown(): void
    {
        if (self::$isLogged || !self::$isLoggingStarted) {
            return;
        }
        self::$isLogged = true;

        // Skip log viewing requests to avoid recursive infinite log loops
        $cleanUri = strtok(self::$uri, '?');
        if (strpos($cleanUri, '/logs') !== false && !isset($_GET['action'])) {
            return;
        }
        if (isset($_GET['action']) && $_GET['action'] === 'clear') {
            return; // Don't log clear operations
        }

        // Skip OPTIONS preflight requests
        if (self::$method === 'OPTIONS') {
            return;
        }

        $durationMs = self::$startTime !== null 
            ? round((microtime(true) - self::$startTime) * 1000, 2) 
            : 0.0;

        $statusCode = http_response_code();
        if ($statusCode === false || $statusCode === 0) {
            $statusCode = 200;
        }

        // Check if there was a fatal error
        $fatal = error_get_last();
        if ($fatal !== null && in_array($fatal['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            self::$capturedFatal = $fatal;
        }

        $isError = ($statusCode >= 400 || self::$capturedException !== null || self::$capturedFatal !== null);

        if ($isError) {
            self::logErrorRequest($statusCode, $durationMs);
        } else {
            self::logSuccessRequest($statusCode, $durationMs);
        }
    }

    /**
     * Log successful request: "cukup log sukses"
     */
    private static function logSuccessRequest(int $statusCode, float $durationMs): void
    {
        $message = sprintf(
            'HTTP %s %s responded %d OK (%sms) [SUCCESS]',
            self::$method,
            self::$uri,
            $statusCode,
            $durationMs
        );

        $entry = [
            'id' => self::nextLogId(),
            'timestamp' => date('c'),
            'level' => 'INF',
            'context' => 'RequestLogger',
            'message' => $message,
            'data' => [
                'method' => self::$method,
                'uri' => self::$uri,
                'statusCode' => $statusCode,
                'durationMs' => $durationMs,
                'ip' => self::getClientIp(),
                'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'query' => !empty(self::$query) ? self::maskSensitiveFields(self::$query) : null,
                'body' => self::$maskedBody,
            ],
            'error' => null,
        ];

        self::appendEntry($entry);
    }

    /**
     * Log errored request: "kasih tau error kenapa. lengkap dengan stacktrace."
     */
    private static function logErrorRequest(int $statusCode, float $durationMs): void
    {
        $level = ($statusCode >= 500 || self::$capturedFatal !== null) ? 'FTL' : 'ERR';
        $errorName = 'HttpError';
        $errorMessage = "Request failed with HTTP {$statusCode}";
        $errorFile = '';
        $errorLine = 0;
        $stackTrace = '';

        if (self::$capturedException !== null) {
            $ex = self::$capturedException;
            $errorName = get_class($ex);
            $errorMessage = $ex->getMessage();
            $errorFile = $ex->getFile();
            $errorLine = $ex->getLine();
            $stackTrace = $ex->getTraceAsString();
        } elseif (self::$capturedFatal !== null) {
            $f = self::$capturedFatal;
            $errorName = 'FatalError';
            $errorMessage = $f['message'];
            $errorFile = $f['file'];
            $errorLine = $f['line'];
            $stackTrace = "Fatal error in {$errorFile} on line {$errorLine}";
        } else {
            // Check output buffer for JSON error message if script returned 4xx/5xx
            $buffer = '';
            if (ob_get_level() > 0) {
                $buffer = ob_get_contents();
            }
            if (!empty($buffer)) {
                $decoded = json_decode($buffer, true);
                if (is_array($decoded)) {
                    if (isset($decoded['message'])) {
                        $errorMessage = $decoded['message'];
                    } elseif (isset($decoded['error'])) {
                        $errorMessage = is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']);
                    }
                    if (isset($decoded['trace']) && is_string($decoded['trace'])) {
                        $stackTrace = $decoded['trace'];
                    }
                    if (isset($decoded['file'])) {
                        $errorFile = $decoded['file'];
                    }
                    if (isset($decoded['line'])) {
                        $errorLine = (int) $decoded['line'];
                    }
                }
            }

            if (empty($stackTrace)) {
                $stackTrace = self::formatBacktrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            }
        }

        $summaryMessage = sprintf(
            'HTTP %s %s failed (%d): %s',
            self::$method,
            self::$uri,
            $statusCode,
            $errorMessage
        );

        $entry = [
            'id' => self::nextLogId(),
            'timestamp' => date('c'),
            'level' => $level,
            'context' => 'RequestLogger',
            'message' => $summaryMessage,
            'data' => [
                'method' => self::$method,
                'uri' => self::$uri,
                'statusCode' => $statusCode,
                'durationMs' => $durationMs,
                'ip' => self::getClientIp(),
                'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'query' => !empty(self::$query) ? self::maskSensitiveFields(self::$query) : null,
                'body' => self::$maskedBody,
            ],
            'error' => [
                'name' => $errorName,
                'message' => $errorMessage,
                'file' => $errorFile ? "{$errorFile}:{$errorLine}" : null,
                'line' => $errorLine ?: null,
                'stack' => $stackTrace,
            ],
        ];

        self::appendEntry($entry);
    }

    /**
     * General Structured Log Methods
     */
    public static function info(string $context, string $message, ?array $data = null): void
    {
        self::write('INF', $context, $message, $data);
    }

    public static function warn(string $context, string $message, ?array $data = null, $error = null): void
    {
        self::write('WRN', $context, $message, $data, $error);
    }

    public static function error(string $context, string $message, ?array $data = null, $error = null): void
    {
        self::write('ERR', $context, $message, $data, $error);
    }

    public static function fatal(string $context, string $message, ?array $data = null, $error = null): void
    {
        self::write('FTL', $context, $message, $data, $error);
    }

    public static function debug(string $context, string $message, ?array $data = null): void
    {
        self::write('DBG', $context, $message, $data);
    }

    private static function write(
        string $level,
        string $context,
        string $message,
        ?array $data = null,
        $error = null
    ): void {
        $errorObj = null;
        if ($error instanceof Throwable) {
            $errorObj = [
                'name' => get_class($error),
                'message' => $error->getMessage(),
                'file' => $error->getFile() . ':' . $error->getLine(),
                'line' => $error->getLine(),
                'stack' => $error->getTraceAsString(),
            ];
        } elseif (is_string($error)) {
            $errorObj = [
                'message' => $error,
                'stack' => self::formatBacktrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)),
            ];
        } elseif (is_array($error)) {
            $errorObj = $error;
        }

        $entry = [
            'id' => self::nextLogId(),
            'timestamp' => date('c'),
            'level' => $level,
            'context' => $context,
            'message' => $message,
            'data' => $data ? self::maskSensitiveFields($data) : null,
            'error' => $errorObj,
        ];

        self::appendEntry($entry);
    }

    /**
     * Write log entry to JSONL storage file.
     */
    private static function appendEntry(array $entry): void
    {
        $file = self::getLogFilePath();
        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($line === false) {
            return;
        }

        @file_put_contents($file, $line . "\n", FILE_APPEND | LOCK_EX);

        // Periodically prune file if larger than 5MB
        if (file_exists($file) && filesize($file) > 5 * 1024 * 1024) {
            self::pruneOldLogs($file);
        }
    }

    /**
     * Retrieve stored logs with filtering and pagination.
     */
    public static function getLogs(array $filter = []): array
    {
        $file = self::getLogFilePath();
        if (!file_exists($file)) {
            return [];
        }

        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || empty($lines)) {
            return [];
        }

        $level = isset($filter['level']) ? strtoupper(trim($filter['level'])) : 'ALL';
        $search = isset($filter['search']) ? strtolower(trim($filter['search'])) : '';
        $limit = isset($filter['limit']) ? min((int) $filter['limit'], 2000) : 500;
        $sinceId = isset($filter['since_id']) ? (int) $filter['since_id'] : 0;

        $results = [];
        $total = count($lines);

        // Read in reverse to get newest entries first
        for ($i = $total - 1; $i >= 0; $i--) {
            $decoded = json_decode($lines[$i], true);
            if (!is_array($decoded)) {
                continue;
            }

            // since_id filter for live streaming
            if ($sinceId > 0 && isset($decoded['id']) && $decoded['id'] <= $sinceId) {
                continue;
            }

            // Level filter
            if ($level !== 'ALL' && isset($decoded['level']) && $decoded['level'] !== $level) {
                continue;
            }

            // Search filter
            if ($search !== '') {
                $msg = strtolower($decoded['message'] ?? '');
                $ctx = strtolower($decoded['context'] ?? '');
                $err = strtolower($decoded['error']['message'] ?? '');
                $stack = strtolower($decoded['error']['stack'] ?? '');
                $uri = strtolower($decoded['data']['uri'] ?? '');

                if (
                    strpos($msg, $search) === false &&
                    strpos($ctx, $search) === false &&
                    strpos($err, $search) === false &&
                    strpos($stack, $search) === false &&
                    strpos($uri, $search) === false
                ) {
                    continue;
                }
            }

            $results[] = $decoded;

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    /**
     * Clear all stored logs.
     */
    public static function clearLogs(): bool
    {
        $file = self::getLogFilePath();
        $ok = @file_put_contents($file, '', LOCK_EX) !== false;
        if ($ok) {
            self::info('System', 'Backend server logs cleared by user');
        }
        return $ok;
    }

    /**
     * Format logs in compact Serilog text output format.
     */
    public static function formatAsText(array $logs): string
    {
        $out = [];
        foreach ($logs as $log) {
            $time = str_replace(['T', 'Z'], [' ', ''], $log['timestamp'] ?? '');
            $lvl = str_pad($log['level'] ?? 'INF', 3, ' ');
            $ctx = !empty($log['context']) ? "[{$log['context']}] " : '';
            $msg = $log['message'] ?? '';

            $line = "[{$time} {$lvl}] {$ctx}{$msg}";

            if (!empty($log['data'])) {
                $line .= "\n  Data: " . json_encode($log['data'], JSON_UNESCAPED_SLASHES);
            }

            if (!empty($log['error'])) {
                $err = $log['error'];
                $line .= "\n  Exception: " . ($err['name'] ? $err['name'] . ': ' : '') . ($err['message'] ?? '');
                if (!empty($err['file'])) {
                    $line .= " in " . $err['file'];
                }
                if (!empty($err['stack'])) {
                    $line .= "\n  Stack: " . $err['stack'];
                }
            }

            $out[] = $line;
        }

        return implode("\n\n", $out);
    }

    private static function nextLogId(): int
    {
        // Use high-resolution timestamp + random integer for unique monotonic ID
        return (int) (microtime(true) * 1000) . mt_rand(10, 99);
    }

    private static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private static function maskSensitiveFields(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'authorization', 'api_key', 'cf_worker_secret', 'jwt_auth_secret'];
        $result = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);
            $isSensitive = false;
            foreach ($sensitiveKeys as $sens) {
                if (strpos($lowerKey, $sens) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $result[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $result[$key] = self::maskSensitiveFields($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private static function formatBacktrace(array $trace): string
    {
        $lines = [];
        foreach ($trace as $i => $frame) {
            $file = $frame['file'] ?? '[internal function]';
            $line = isset($frame['line']) ? "({$frame['line']})" : '';
            $class = $frame['class'] ?? '';
            $type = $frame['type'] ?? '';
            $function = $frame['function'] ?? '';

            $lines[] = "#{$i} {$file}{$line}: {$class}{$type}{$function}()";
        }
        return implode("\n", $lines);
    }

    private static function pruneOldLogs(string $file): void
    {
        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false && count($lines) > 3000) {
            $keptLines = array_slice($lines, -3000);
            @file_put_contents($file, implode("\n", $keptLines) . "\n", LOCK_EX);
        }
    }
}
