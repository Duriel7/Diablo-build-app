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

    public function login(string $email, string $password): string {
        $user = $this->userRepository->SqlGetUserByEmail($email);

        if (!$user) {
            throw new \Exception("Invalid email.");
        }

        if (!password_verify($password, $user->getPasswordHashed())) {
            throw new \Exception("Invalid password.");
        }

        return $this->jwtService->createToken([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ]);
    }
}