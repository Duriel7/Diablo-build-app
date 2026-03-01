<?php
namespace Diablo\Controller\Admin;

use Diablo\Core\Request;
use Diablo\Repository\UserRepository;

class UserController extends AbstractAdminController
{
    public function __construct(
        Request $request,
        private UserRepository $userRepository
    ) {
        parent::__construct($request);
    }

    public function index(): void
    {
        $users = $this->userRepository->SqlGetAllUsers(50);
        $this->render('users/index.html.twig', ['users' => $users]);
    }
    
    public function deleteUser(int $id): void {
        $this->userRepository->SqlDeleteUser($id);
        $this->redirect('/admin/users?success=deleted');
    }
}