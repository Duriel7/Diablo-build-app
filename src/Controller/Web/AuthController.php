<?php

namespace Diablo\Controller\Web;

use Diablo\Core\Request;
use Diablo\Service\AuthService;

class AuthController extends AbstractWebController
{
    public function __construct(
        Request $request,
        private AuthService $authService
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
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        $user = $this->authService->verifyCredentials($email, $password);

        if ($user) {
            $_SESSION['user'] = [
                'id' => $user->getId(),
                'nickname' => $user->getNickname(),
                'role' => $user->getRole()
            ];
            if (strtolower($user->getRole()) === 'admin') {
                $this->redirect('/admin/dashboard');
            } else {
                $this->redirect('/profile');
            }
        } else {
            $this->render('auth/login.html.twig', [
                'error' => 'Identifiants invalides.'
            ]);
        }
    }

    //Logout
    public function logout(): void {
        session_destroy();
        $this->redirect('/');
    }
}