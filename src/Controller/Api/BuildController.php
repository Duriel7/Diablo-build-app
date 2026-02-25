<?php

namespace Diablo\Controller\Api;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;
use Diablo\Model\Build;
use Diablo\Controller\Api\AbstractApiController;

class BuildController extends AbstractApiController
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
            $this->error('Missing required fields', 400);
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
            $this->error('Failed to create build', 500);
        }

        $this->success(['id' => $id, 'message' => 'Build created'], 201);
    }

    /**
     * Mettre à jour un build (Seulement si l'utilisateur est l'auteur)
     */
    public function update(int $id): void
    {
        $data = $this->request->getJson();
        $build = $this->buildRepository->SqlGetBuildById($id);

        if (!$build) {
            $this->error('Build not found', 404);
        }

        // Sécurité : Vérifier si l'utilisateur est l'auteur ou un admin
        if ($build->getAuthorId() !== $this->auth['id'] && $this->auth['role'] !== 'admin') {
            $this->error('Unauthorized: You are not the author', 403);
        }

        // Mise à jour sélective
        if (isset($data['name'])) $build->setName($data['name']);
        if (isset($data['description'])) $build->setDescription($data['description']);
        
        $build->setVersion($build->getVersion() + 1); // On incrémente la version
        $build->setUpdatedAt(new \DateTime());

        $success = $this->buildRepository->SqlUpdateBuild($build);

        if (!$success) {
            $this->error('Update failed', 500);
        }

        $this->success(['message' => 'Build updated']);
    }

    /**
     * Supprimer un build
     */
    public function delete(int $id): void
    {
        $build = $this->buildRepository->SqlGetBuildById($id);

        if (!$build) {
            $this->error('Build not found', 404);
        }

        // Même vérification de sécurité
        if ($build->getAuthorId() !== $this->auth['id'] && $this->auth['role'] !== 'admin') {
            $this->error('Unauthorized', 403);
        }

        $this->buildRepository->SqlDeleteBuild($id);
        $this->success(['message' => 'Build deleted']);
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
}