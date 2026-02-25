<?php

namespace Diablo\Security;

use Diablo\Service\JwtService;

class JwtMiddleware {
    public function __construct(private JwtService $jwtService) {}

    public function requireAuth(): array
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if (!$auth) {
            $this->unauthorized('Missing Authorization header');
        }

        if (!preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $auth, $m)) {
            $this->unauthorized('Invalid Authorization format');
        }

        $token = $m[1];

        try {
            $decoded = $this->jwtService->decode($token);

            if (!$this->jwtService->validate($decoded)) {
                $this->unauthorized('Invalid or expired token');
            }

            if (!isset($decoded->data->id, $decoded->data->email, $decoded->data->role)) {
                $this->unauthorized('Token payload missing');
            }

            return [
                'id' => (int) $decoded->data->id,
                'email' => (string) $decoded->data->email,
                'role' => (string) $decoded->data->role,
            ];
        } catch (\Throwable $e) {
            $this->unauthorized('Invalid or expired token');
        }
    }

    private function unauthorized(string $message): never
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}