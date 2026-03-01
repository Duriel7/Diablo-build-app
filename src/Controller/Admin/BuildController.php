<?php
namespace Diablo\Controller\Admin;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;

class BuildController extends AbstractAdminController
    {
    public function __construct(
        Request $request,
        private BuildRepository $buildRepository
    ) {
        parent::__construct($request);
    }

    public function index(): void {
        $builds = $this->buildRepository->SqlGetAllBuilds(50);
        $this->render('builds/index.html.twig', ['builds' => $builds]);
    }
    
    public function deleteBuild(int $id): void {
        $this->buildRepository->SqlDeleteBuild($id);
        $this->redirect('/admin/builds?success=deleted');
    }
}