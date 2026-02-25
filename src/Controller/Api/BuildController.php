<?php

namespace Diablo\Controller\Api;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;
use Diablo\Model\Build;

class BuildController
{
    public function __construct(
        private Request $request,
        private BuildRepository $buildRepository,
        private array $auth
    ) {}

    //Create function that will call the SQL method to enter build data in DB
    public function create(): void {
        $data = $this->request->getJson();

        // Validation minimale
        if (
            !isset($data['name']) ||
            !isset($data['characterClass']) ||
            !isset($data['description']) ||
            !isset($data['game'])
        ) {
            $this->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $build = new Build();

        $build->setName($data['name']);
        $build->setCharacterClass($data['characterClass']);
        $build->setDescription($data['description']);
        $build->setAuthorId($this->auth['id']);
        $build->setGame($data['game']);
        $build->setIsDraft($data['isDraft'] ?? false);
        $build->setVersion(1);
        $build->setCreatedAt(new \DateTimeImmutable());
        $build->setUpdatedAt(null);
        $build->setImageRepository($data['imageRepository'] ?? '');
        $build->setImageFileName($data['imageFileName'] ?? '');

        $id = $this->buildRepository->SqlCreateBuild($build);

        if (!$id) {
            $this->json([
                'success' => false,
                'message' => 'Failed to create build'
            ], 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Build created',
            'id' => $id
        ], 201);
    }

    public function index(): void {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 10);

        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 10;

        $offset = ($page - 1) * $limit;
        
        $total = $this->buildRepository->SqlCountBuilds();
        $totalPages = (int) ceil($total / $limit);

        $builds = $this->buildRepository->SqlGetAllBuildsPaginated($limit, $offset);

        $this->json([
            'success' => true,
            'page' => $page,
            'limit' => $limit,
            'data' => $builds,
            'total' => $total,
            'totalPages' => $totalPages,
        ]);
    }

    private function json(array $data, int $status = 200): never {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}