<?php

namespace Diablo\Service;

use Diablo\Repository\UserRepository;
use Diablo\Model\User;

class AuthService
{
    private UserRepository $userRepository;
    private JwtService $jwtService;

    public function __construct(
        UserRepository $userRepository,
        JwtService $jwtService
    ) {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    public function login(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception("Invalid credentials.");
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new \Exception("Invalid credentials.");
        }

        return $this->jwtService->createToken([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ]);
    }
}