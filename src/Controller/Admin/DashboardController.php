<?php
namespace Diablo\Controller\Admin;

use Diablo\Repository\BuildRepository;
use Diablo\Repository\UserRepository;
use Diablo\Core\Request;

class DashboardController extends AbstractAdminController
{
    public function __construct(
        Request $request,
        private BuildRepository $buildRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct($request);
    }

    public function index(): void
    {
        $this->render('admin/dashboard.html.twig', [
            'totalBuilds' => $this->buildRepository->SqlCountBuilds(),
            'totalUsers' => count($this->userRepository->SqlGetAllUsers(1000)),
            'lastBuilds' => $this->buildRepository->SqlGetAllBuildsPaginated(5, 0)
        ]);
    }
}