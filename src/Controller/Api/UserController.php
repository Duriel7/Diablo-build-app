<?php

namespace Diablo\Controller\Api;

use Diablo\Core\Request;
use Diablo\Repository\UserRepository;
use Diablo\Model\User;

class UserController extends AbstractApiController
{
    public function __construct(
        Request $request,
        private UserRepository $userRepository
    ) {
        parent::__construct($request);
    }

    //New user subscription function
    public function register(): void
    {
        $data = $this->request->getJson();

        //Validate mandatory fields
        if (!isset($data['nickname'], $data['city'], $data['email'], $data['password'])) {
            $this->error('Missing required fields (email, password, nickname, city)', 400);
        }

        //Check if email alreayd exists
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

        $this->success(['id' => $id, 'message' => 'User registered successfully'], 201);
    }

    //Take user profile
    public function profile(array $authData): void
    {
        $user = $this->userRepository->SqlGetUserById($authData['id']);

        if (!$user) {
            $this->error('User not found', 404);
        }

        $this->success(['user' => $user]);
    }
}