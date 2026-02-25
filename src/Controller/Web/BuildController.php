<?php

namespace Diablo\Controller\Web;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;

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
        // 1. On récupère le numéro de page depuis l'URL (ex: /builds?page=2)
        $page = (int)($this->request->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // 2. On récupère les builds avec la jointure Auteur
        $builds = $this->buildRepository->SqlGetAllBuildsPaginated($limit, $offset);
        
        // 3. On compte le total pour savoir combien de pages afficher
        $totalBuilds = $this->buildRepository->SqlCountBuilds();
        $totalPages = ceil($totalBuilds / $limit);

        $this->render('builds/index.html.twig', [
            'builds' => $builds,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Affiche le détail d'un build spécifique
     */
    public function show(int $id): void
    {
        $build = $this->buildRepository->SqlGetBuildById($id);

        if (!$build) {
            // Tu pourrais créer une vue 404.html.twig
            $this->render('errors/404.html.twig', ['message' => 'Build introuvable']);
        }

        $this->render('builds/show.html.twig', [
            'build' => $build
        ]);
    }
}