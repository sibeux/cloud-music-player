<?php
use \Firebase\JWT\JWT;

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
        'sub' => $user['id'], // 'sub' adalah standar klaim untuk User ID
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
        'sub' => $user['id'],
    ];
    $refreshToken = JWT::encode($refreshPayload, $secretKey, 'HS256');

    return [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken
    ];
}

function saveToDatabase($db, $userId, $tokenHash, $jti, $exp){
    try{
        // Konversi timestamp 'exp' dari JWT ke format DATETIME MySQL
        $expiresAt = date('Y-m-d H:i:s', $exp);

        $sql = "INSERT INTO refresh_tokens
                (user_id, token_hash, jti, expires_at, is_revoked)
                VALUES
                (:user_id, :token_hash, :jti, :expires_at, 0)";
        
        $stmt = $db->prepare($sql);

        $result = $stmt->execute([
            ':user_id'    => $userId,
            ':token_hash' => $tokenHash,
            ':jti'        => $jti,
            ':expires_at' => $expiresAt
        ]);

        return $result;

    } catch (PDOException $e) {
        error_log("DB Prep Error: " . $e->getMessage());
        return false;
    }
}