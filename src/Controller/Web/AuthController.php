<?php

namespace Diablo\Controller\Web;

use Diablo\Core\Request;
use Diablo\Service\AuthService;
use Diablo\Repository\UserRepository;
use Diablo\Model\User;

class AuthController extends AbstractWebController
{
    public function __construct(
        Request $request,
        private AuthService $authService,
        private UserRepository $userRepository
    ) {
        parent::__construct($request);
    }

    //Displays login form
    public function loginForm(): void {
        if (isset($_SESSION['user'])) {
            $this->redirect('/');
        }

        $this->render('users/login.html.twig');
    }

    //Form treatment for login
    public function login(): void {
        if ($this->request->getMethod() === 'POST') {
            $email = $this->request->post('email');
            $password = $this->request->post('password');

            if ($email && $password) {
                $user = $this->authService->verifyCredentials($email, $password);

                if ($user) {
                    $_SESSION['user'] = [
                        'id' => $user->getId(),
                        'nickname' => $user->getNickname(),
                        'role' => $user->getRole()
                    ];
                    
                    if (strtolower($user->getRole()) === 'admin') {
                        $this->redirect('/');
                    } else {
                        $this->redirect('/');
                    }
                    return;
                }
            }
            
            $this->render('users/login.html.twig', ['error' => 'Identifiants invalides.']);
            return;
        }

        $this->render('users/login.html.twig');
    }
    
    public function register(): void {
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();

            $avatarBase64 = $data['avatarBase64'] ?? null;
            $repo = 'default';
            $fileName = 'user.png';

            if (!empty($avatarBase64)) {
                $repo = date('Y/m');
                $fileName = uniqid() . '.jpg';
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $repo;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                file_put_contents($uploadDir . '/' . $fileName, base64_decode($avatarBase64));
            }

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
            $user->setAvatarRepository($repo);
            $user->setAvatarFileName($fileName);

            $id = $this->userRepository->SqlCreateUser($user);

            if ($id) {
                $_SESSION['user'] = [
                    'id' => $id,
                    'nickname' => $user->getNickname(),
                    'role' => $user->getRole()
                ];
                $this->redirect('/profile');
                return;
            } else {
                $this->render('users/register.html.twig', ['error' => 'Erreur lors de la création du compte']);
                return;
            }
        }

        $this->render('users/register.html.twig');
    }

    //User profile view for themselves
    public function profile(): void {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userRepository->SqlGetUserById($_SESSION['user']['id']);

        $this->render('users/profile.html.twig', [
            'user' => $user,
            'isOwner' => true
        ]);
    }

    //User profile view for others
    public function showProfile(int $id): void {
        $user = $this->userRepository->SqlGetUserById($id);
        
        if (!$user) {
            $this->render('errors/404.html.twig', ['message' => 'Ce Nephalem est introuvable.']);
            return;
        }

        $this->render('users/profile.html.twig', [
            'user' => $user,
            'isOwner' => (isset($_SESSION['user']) && $_SESSION['user']['id'] === $id)
        ]);
    }

    //Edit profile methods - GET then POST
    public function editProfile(): void {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userRepository->SqlGetUserById($_SESSION['user']['id']);
        
        $this->render('users/edit.html.twig', [
            'user' => $user
        ]);
    }
    public function updateProfile(): void {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $data = $this->request->getPost();
        $user = $this->userRepository->SqlGetUserById($_SESSION['user']['id']);

        if (!$user) {
            $this->redirect('/');
            return;
        }

        if (!empty($data['password'])) {
            $user->setPasswordHashed(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        if (!empty($data['avatarBase64'])) {
            //Delete old avatar before adding new one
            if ($user->getAvatarFileName() !== 'user.png') {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $user->getAvatarRepository() . '/' . $user->getAvatarFileName();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $repo = date('Y/m');
            $fileName = uniqid() . '.jpg';
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $repo;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            file_put_contents($uploadDir . '/' . $fileName, base64_decode($data['avatarBase64']));
            
            $user->setAvatarRepository($repo);
            $user->setAvatarFileName($fileName);
        }

        $user->setNickname($data['nickname']);
        $user->setBio($data['bio'] ?? null);
        $user->setCity($data['city']);
        $user->setEmail($data['email']);

        if ($this->userRepository->SqlUpdateUser($user)) {
            $_SESSION['user']['nickname'] = $user->getNickname();
            $this->redirect('/profile?success=1');
        } else {
            $this->render('users/edit.html.twig', [
                'user' => $user,
                'error' => 'Erreur lors de la mise à jour.'
            ]);
        }
    }

    //Logout
    public function logout(): void {
        session_destroy();
        $this->redirect('/');
    }
}