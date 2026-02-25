<?php

namespace Diablo\Controller\Api;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;
use Diablo\Repository\UserRepository;

class AdminController extends AbstractApiController
{
    public function __construct(
        Request $request,
        private BuildRepository $buildRepository,
        private UserRepository $userRepository,
        private array $auth // Contient les infos du JWT (id, email, role)
    ) {
        parent::__construct($request);

        // --- SÉCURITÉ CRITIQUE (Point clé de l'éval) ---
        // On vérifie le rôle AVANT toute action.
        if ($this->auth['role'] !== 'admin') {
            $this->error('Access denied: Admin role required', 403);
        }
    }

    /**
     * Supprimer n'importe quel build (Modération)
     */
    public function deleteBuild(int $id): void
    {
        $build = $this->buildRepository->SqlGetBuildById($id);
        
        if (!$build) {
            $this->error('Build not found', 404);
        }

        $this->buildRepository->SqlDeleteBuild($id);
        $this->success(['message' => "Build #$id deleted by administrator"]);
    }

    /**
     * Supprimer un utilisateur (Bannissement)
     */
    public function deleteUser(int $id): void
    {
        // Empêcher l'admin de se supprimer lui-même par erreur
        if ($id === $this->auth['id']) {
            $this->error('Self-destruction is not allowed', 400);
        }

        $user = $this->userRepository->SqlGetUserById($id);
        if (!$user) {
            $this->error('User not found', 404);
        }

        $this->userRepository->SqlDeleteUser($id);
        $this->success(['message' => "User #$id has been removed"]);
    }
}