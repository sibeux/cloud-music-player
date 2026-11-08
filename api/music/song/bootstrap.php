<?php

if (!ob_get_level() && php_sapi_name() !== 'cli') {
    ob_start('ob_gzhandler');
}
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../../utils/utils.php';
require_once __DIR__ . '/SongDTO.php';

function authenticateSongUser($secretKey)
{
    $auth = new BearerAuth($secretKey);
    $user = $auth->validate(false);
    $userId = isset($user['sub']) ? (int) $user['sub'] : 0;
    $role = isset($user['data']['role']) ? (string) $user['data']['role'] : 'free';

    return [$userId, $role];
}

function sendSongJsonError($status, $error, $message)
{
    http_response_code($status);
    echo json_encode([
        'status' => 'error',
        'error' => $error,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
