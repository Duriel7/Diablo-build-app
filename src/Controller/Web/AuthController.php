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

        $this->render('auth/login.html.twig');
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

            $user = new User();
            $user->setNickname($data['nickname']);
            $user->setEmail($data['email']);
            $user->setCity($data['city']);
            $user->setPasswordHashed(password_hash($data['password'], PASSWORD_BCRYPT));
            $user->setRole('user');

            $id = $this->userRepository->SqlCreateUser($user);

            if ($id) {
                $this->redirect('/login');
            } else {
                $this->render('users/register.html.twig', ['error' => 'Erreur lors de la création du compte']);
            }
            return;
        }

        $this->render('users/register.html.twig');
    }

    //Logout
    public function logout(): void {
        session_destroy();
        $this->redirect('/');
    }
}