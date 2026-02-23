<?php

namespace Diablo\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secret;
    private string $issuer;
    private int $ttl;

    public function __construct(string $secret, string $issuer, int $ttl = 900)
    {
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->ttl = $ttl;
    }

    public function createToken(array $payload): string
    {
        $now = time();

        $data = [
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttl,
            'iss' => $this->issuer,
            'data' => $payload
        ];

        return JWT::encode($data, $this->secret, 'HS512');
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, 'HS512'));
    }

    public function validate(object $decodedToken): bool
    {
        $now = time();

        return (
            $decodedToken->iss === $this->issuer &&
            $decodedToken->nbf <= $now &&
            $decodedToken->exp >= $now
        );
    }
}