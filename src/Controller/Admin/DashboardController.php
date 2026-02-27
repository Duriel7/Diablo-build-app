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

    public function index(): void {
        $usersCount = count($this->userRepository->SqlGetAllUsers(100));
        $buildsCount = count($this->buildRepository->SqlGetAllBuilds(100));

        $this->render('admin/index.html.twig', [
            'usersCount' => $usersCount,
            'buildsCount' => $buildsCount,
            'lastBuilds' => $this->buildRepository->SqlGetAllBuildsPaginated(5, 0)
        ]);
    }
}