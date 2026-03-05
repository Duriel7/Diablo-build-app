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

    //See user profile
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