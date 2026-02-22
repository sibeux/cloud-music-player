<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function helperRefreshMethod($user, $secretKey, $db)
{
    // Generate token secara otomatis
    $token = generateToken($user, $secretKey, $db);
    
    // Hash refresh token
    $hashed_token = hash('sha256', $token['refresh_token']);

    // Ambil JTI dan EXP dari payload untuk sinkronisasi DB
    $refreshPayload = JWT::decode($token['refresh_token'], new Key($secretKey, 'HS256'));
    $jti = $refreshPayload->jti;
    $exp = $refreshPayload->exp;

    // Simpan Hash ke Database (Jangan simpan plain text!)
    $success = saveToDatabase($db, $user['user_id'], $hashed_token, $jti, $exp);

    if (!$success) {
        return [
            'status' => 'error',
            'message' => 'Gagal menyimpan token ke database.'
        ];
    }

    return [
        'status' => 'success',
        'access_token' => $token['access_token'],
        'refresh_token' => $token['refresh_token']
    ];
}

function generateToken($user, $secretKey, $db)
{
    $issuedAt = time();
    $expirationTime = $issuedAt + (60 * 15); // 15 minutes
    $issuer = "https://sibeux.my.id";

    // Generate Access Token
    $accessPayload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'iss' => $issuer,
        'sub' => $user['user_id'], // 'sub' adalah standar klaim untuk User ID
        'data' => [
            'role' => $user['role'],
            'email' => $user['email'],
            'name' => $user['name'],
        ],
    ];
    
    $accessToken = JWT::encode($accessPayload, $secretKey, 'HS256');

    // Generate Refresh Token
    // Gunakan random string unik (JTI) agar tidak bisa ditebak
    $jti = bin2hex(random_bytes(32));
    $refreshPayload = [
        'iat' => $issuedAt,
        'exp' => $issuedAt + (60 * 60 * 1), // (1 Jam)
        'iss' => $issuer,
        'jti' => $jti, // ID unik token ini
        'sub' => $user['user_id'],
    ];
    $refreshToken = JWT::encode($refreshPayload, $secretKey, 'HS256');

    return [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken
    ];
}

function saveToDatabase($db, $userId, $tokenHash, $jti, $exp) {
    try {
        // Konversi timestamp 'exp' dari JWT ke format DATETIME MySQL
        $expiresAt = date('Y-m-d H:i:s', $exp);

        // Bersihkan token lama milik user ini yang sudah expired
        // Agar DB tidak penuh sampah tanpa mengganggu session aktif di device lain
        $cleanupSql = "DELETE FROM refresh_tokens WHERE user_id = ? AND expires_at < NOW()";
        $cleanStmt = $db->prepare($cleanupSql);
        $cleanStmt->bind_param("i", $userId);
        $cleanStmt->execute();
        $cleanStmt->close();

        $sql = "INSERT INTO refresh_tokens
                (user_id, token_hash, jti, expires_at, is_revoked)
                VALUES
                (?, ?, ?, ?, 0)";
        
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        /**
         * bind_param menjelaskan tipe data:
         * i = integer (untuk userId)
         * s = string (untuk tokenHash, jti, dan expiresAt)
         */
        $stmt->bind_param("isss", $userId, $tokenHash, $jti, $expiresAt);

        $result = $stmt->execute();
        $stmt->close();

        return $result;

    } catch (Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        return false;
    }
}