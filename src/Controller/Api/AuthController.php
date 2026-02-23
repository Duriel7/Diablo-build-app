<?php

namespace Diablo\Controller\Api;

use Diablo\Controller\AbstractController;
use Diablo\Service\AuthService;
use Diablo\Core\Request;

class AuthController extends AbstractController {
    private AuthService $authService;

    public function __construct(Request $request, AuthService $authService)
    {
        parent::__construct($request);
        $this->authService = $authService;
    }

    public function login()
    {
        $data = $this->request->getJson();

        if (!isset($data['email'], $data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email and password required.'
            ], 400);
        }

        try {
            $token = $this->authService->login(
                $data['email'],
                $data['password']
            );

            return $this->json([
                'success' => true,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }
    }
}