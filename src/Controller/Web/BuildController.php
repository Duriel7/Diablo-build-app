<?php

namespace Diablo\Controller\Web;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;
use Diablo\Model\Build;

class BuildController extends AbstractWebController
{
    public function __construct(
        Request $request,
        private BuildRepository $buildRepository
    ) {
        parent::__construct($request);
    }

    //Index shows builds list with search form
    public function index(): void {
        $page = (int)($this->request->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $builds = $this->buildRepository->SqlGetAllBuildsPaginated($limit, $offset);
        
        $totalBuilds = $this->buildRepository->SqlCountBuilds();
        $totalPages = ceil($totalBuilds / $limit);

        $this->render('builds/index.html.twig', [
            'builds' => $builds,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    //Displays build details
    public function show(int $id): void {
        $build = $this->buildRepository->SqlGetBuildById($id);

        if (!$build) {
            $this->render('errors/404.html.twig', ['message' => 'Build introuvable']);
        }

        $this->render('builds/show.html.twig', [
            'build' => $build
        ]);
    }

    public function create(): void {
        //Show form to create a build
        $this->render('builds/create.html.twig');
    }
    
    public function store(): void {
        $userSession = $_SESSION['user'] ?? null;

        if (!$userSession) {
            $this->redirect('/login');
            return;
        }

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();
            
            $build = new Build();
            $build->setName($data['name']);
            $build->setCharacterClass($data['characterClass']);
            $build->setDescription($data['description']);
            $build->setGame($data['game']);
            
            $build->setAuthorId($userSession['id']); 
            
            $build->setVersion(1);
            $build->setCreatedAt(new \DateTimeImmutable());

            $id = $this->buildRepository->SqlCreateBuild($build);

            if ($id) {
                $this->redirect('/builds');
            } else {
                $this->render('builds/create.html.twig', ['error' => 'Erreur de forge']);
            }
        }
    }
}