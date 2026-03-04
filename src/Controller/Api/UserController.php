<?php

namespace Diablo\Controller\Api;

use Diablo\Core\Request;
use Diablo\Repository\UserRepository;
use Diablo\Model\User;
use Diablo\Service\MailerService;

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

        $notifService = new MailerService();
        $notifService->sendWelcomeEmail($user->getEmail(), $user->getNickname());

        $userData = [
            'id' => $id,
            'nickname' => $user->getNickname(),
            'role' => $user->getRole(),
            'avatar' => $user->getAvatarRepository() . '/' . $user->getAvatarFileName(),
            'email' => $user->getEmail()
        ];

        $this->success(['user' => $userData, 'message' => 'User registered successfully'], 201);
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

    public function update(array $authData): void {
        $data = $this->request->getJson();
        $user = $this->userRepository->SqlGetUserById($authData['id']);

        if (!$user) {
            $this->error('User not found', 404);
        }

        if (!empty($data['password'])) {
            $user->setPasswordHashed(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        //Avatar handling for Flutter
        if (!empty($data['avatarBase64'])) {
            //Delete old one if not default
            if ($user->getAvatarFileName() !== 'user.png') {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $user->getAvatarRepository() . '/' . $user->getAvatarFileName();
                if (file_exists($oldPath)) { unlink($oldPath); }
            }

            $repo = date('Y/m');
            $fileName = uniqid() . '.jpg';
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $repo;

            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

            file_put_contents($uploadDir . '/' . $fileName, base64_decode($data['avatarBase64']));
            
            $user->setAvatarRepository($repo);
            $user->setAvatarFileName($fileName);
        }

        if (isset($data['nickname'])) $user->setNickname($data['nickname']);
        if (isset($data['bio'])) $user->setBio($data['bio']);
        if (isset($data['city'])) $user->setCity($data['city']);
        if (isset($data['email'])) $user->setEmail($data['email']);

        if ($this->userRepository->SqlUpdateUser($user)) {
            $this->success(['message' => 'Profile updated successfully']);
        } else {
            $this->error('Database update failed', 500);
        }
    }
}