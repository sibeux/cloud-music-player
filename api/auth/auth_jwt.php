<?php
use \Firebase\JWT\JWT;

function generateToken($user, $secretKey)
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

    // Simpan Hash ke Database (Jangan simpan plain text!)
    // saveToDatabase($user['id'], hash('sha256', $refreshToken), $jti);

    return [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken
    ];
}