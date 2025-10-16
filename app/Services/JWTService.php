<?php

namespace App\Services;

use Firebase\JWT\JWT;

class JWTService {
    //Here we will put all the methods to create and validate JWT tokens
    private string $secretKey;
    private string $refreshSecretKey;
    private string $algorithm = 'HS256';

    public function __construct(){
        $this->secretKey = config('app.key');
        $this->refreshSecretKey = config('app.key').'_refresh';
    }

    //Generate access_token
    public function generateAccessToken(array $payload, int $expiresIn = 3600): string{
        $issuedAt = time();
        $expire = $issuedAt + $expiresIn; // The token  will expire in 1 hour

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
            'type' => 'access_token'
        ]);

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    //Generate refresh token
    public function generateRefreshToken(array $payload, int $expiresIn=604800): string{
        $issuedAt = time();
        $expire = $issuedAt + $expiresIn; // The token will expire in 7 days

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
            'type' => 'refresh_token'
        ]);

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    //Generate both types of tokens using the methods we have created above

    public function generateTokenPair(array $payload): array{
        return [
            'access_token' => $this->generateAccessToken($payload),
            'refresh_token' => $this->generateRefreshToken($payload)
        ];
    }


}


?>
