<?php

namespace Diablo\Controller\Api;

use Diablo\Controller\Api\AbstractApiController;
use Diablo\Service\AuthService;
use Diablo\Core\Request;
use Diablo\Model\User;
use Diablo\Repository\UserRepository;
use Diablo\Service\JwtService;
use Diablo\Service\MailerService;

class AuthController extends AbstractApiController {

    public function __construct(Request $request, private UserRepository $userRepository, private AuthService $authService, private JwtService $jwtService)
    {
        parent::__construct($request);
        $this->userRepository = $userRepository;
        $this->authService = $authService;
        $this->jwtService = $jwtService;
    }

    //New user registration function
    public function register(): void
    {
        $data = $this->request->getJson();

        //Validate mandatory fields
        if (!isset($data['nickname'], $data['city'], $data['email'], $data['password'])) {
            $this->error('Missing required fields (email, password, nickname, or city)', 400);
        }

        //Check if email already exists
        if ($this->userRepository->SqlGetUserByEmail($data['email'])) {
            $this->error('Email already exists', 409);
        }

        //User object creation
        $user = new User();
        $user->setNickname($data['nickname']);
        $user->setBio($data['bio'] ?? null);
        $user->setRegisteredAt(new \DateTimeImmutable());
        $user->setCity($data['city']);
        $user->setLatitude($data['latitude'] ?? 0.0);
        $user->setLongitude($data['longitude'] ?? 0.0);
        $user->setEmail($data['email']);
        $user->setPasswordHashed(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setRole(strtolower('user'));
        $user->setAvatarRepository($data['avatarRepository'] ?? 'default');
        $user->setAvatarFileName($data['avatarFileName'] ?? 'user.png');

        $id = $this->userRepository->SqlCreateUser($user);

        if (!$id) {
            $this->error('Failed to create account', 500);
        }

        //Token creation
        $payload = [
            'id' => $id,
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname()
        ];
        $token = $this->jwtService->createToken($payload);

        //Welcoming email
        $notifService = new MailerService();
        $notifService->sendWelcomeEmail($user->getEmail(), $user->getNickname());

        $userData = [
            'id' => $id,
            'nickname' => $user->getNickname(),
            'role' => $user->getRole(),
            'avatar' => $user->getAvatarRepository() . '/' . $user->getAvatarFileName(),
            'email' => $user->getEmail()
        ];

        $this->success(['token' => $token, 'user' => $userData, 'message' => 'User registered successfully'], 201);
    }

    //Login function
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

            $user = $this->userRepository->SqlGetUserByEmail($data['email']);

            $userData = [
                'id' => $user->getId(),
                'nickname' => $user->getNickname(),
                'role' => $user->getRole(),
                'avatar' => $user->getAvatarRepository() . '/' . $user->getAvatarFileName(),
                'email' => $user->getEmail()
            ];

            return $this->success([
                'token' => $token, 
                'user' => $userData, 
                'message' => 'Logged in successfully'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Auth Controller - Invalid credentials. ' . $e->getMessage()
            ], 401);
        }
    }
}