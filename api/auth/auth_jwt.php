<?php
use \Firebase\JWT\JWT;

function generateToken($id, $email, $name, $role, $secretKey)
{
    $key = $secretKey;
    $issuedAt = time();
    $expirationTime = $issuedAt + (60 * 15); // 15 minutes
    $issuer = "https://sibeux.my.id";

    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'iss' => $issuer,
        'data' => [
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'role' => $role,
        ],
    ];

    return JWT::encode($payload, $key, 'HS256');
}