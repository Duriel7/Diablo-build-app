<?php

namespace Diablo\Controller\Web;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;

class HomeController extends AbstractWebController
{
    public function __construct(
        Request $request,
        private BuildRepository $buildRepository
    ) {
        parent::__construct($request);
    }

    public function index(): void {
        //Limit = 3, Offset = 0
        $latestBuilds = $this->buildRepository->SqlGetAllBuildsPaginated(3, 0);

        $this->render('home/index.html.twig', [
            'latestBuilds' => $latestBuilds
        ]);
    }
}