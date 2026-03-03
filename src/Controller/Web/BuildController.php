<?php

namespace Diablo\Controller\Web;

use Diablo\Core\Request;
use Diablo\Repository\BuildRepository;
use Diablo\Model\Build;
use Diablo\Repository\UserRepository;
use Diablo\Service\MailerService;
use Diablo\Service\PdfService;

class BuildController extends AbstractWebController
{
    public function __construct(
        Request $request,
        private BuildRepository $buildRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct($request);
    }

    //Index shows builds list with search form
    public function index(): void {
        $searchTerm = $this->request->get('search', '');
        $page = (int)($this->request->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $builds = $this->buildRepository->SqlGetAllBuildsPaginated($searchTerm, $limit, $offset);
        
        $totalBuilds = $this->buildRepository->SqlCountBuilds($searchTerm);
        $totalPages = ceil($totalBuilds / $limit);

        $this->render('builds/index.html.twig', [
            'builds' => $builds,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchTerm' => $searchTerm
        ]);
    }

    //Displays build details
    public function show(int $id): void {
        $build = $this->buildRepository->SqlGetBuildById($id);
        $author = $this->userRepository->SqlGetUserById($build->getAuthorId());

        if (!$build) {
            $this->render('errors/404.html.twig', ['message' => 'Build introuvable']);
        }

        $this->render('builds/show.html.twig', [
            'build' => $build,
            'author' => $author
        ]);
    }

    //Show form to create a build
    public function create(): void {
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
            $build->setUpdatedAt(null);
            $build->setIsDraft(false);
            $build->setImageRepository('');
            $build->setImageFileName('');

            if (!empty($data['imageBase64'])) {
                $repo = date('Y/m');
                $fileName = uniqid() . '.jpg';
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/builds/' . $repo;

                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                file_put_contents($uploadDir . '/' . $fileName, base64_decode($data['imageBase64']));
                
                $build->setImageRepository($repo);
                $build->setImageFileName($fileName);
            } else {
                $build->setImageRepository('');
                $build->setImageFileName('');
            }

            $id = $this->buildRepository->SqlCreateBuild($build);

            if ($id) {
                $this->redirect('/builds/' . $id);
            } else {
                $this->render('builds/create.html.twig', [
                    'error' => 'La forge a échoué. Vérifiez vos cristaux de sang (champs invalides).'
                ]);
            }
        }
    }

    //Edit build form
    public function edit(int $id): void {
        $build = $this->buildRepository->SqlGetBuildById($id);
        
        if (!$build || ($build->getAuthorId() !== ($_SESSION['user']['id'] ?? null) && $_SESSION['user']['role'] !== 'admin')) {
            $this->redirect('/builds');
            return;
        }

        $this->render('builds/edit.html.twig', ['build' => $build]);
    }
    public function updateBuild(int $id): void {
        $userSession = $_SESSION['user'] ?? null;
        if (!$userSession) {
            $this->redirect('/login');
            return;
        }

        $build = $this->buildRepository->SqlGetBuildById($id);

        if (!$build || ($build->getAuthorId() !== $userSession['id'] && $userSession['role'] !== 'admin')) {
            $this->redirect('/builds');
            return;
        }

        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();

            $build->setName($data['name']);
            $build->setCharacterClass($data['characterClass']);
            $build->setDescription($data['description']);
            $build->setGame($data['game']);
            $build->setVersion($build->getVersion() + 1);
            $build->setUpdatedAt(new \DateTime());

            if (!empty($data['imageBase64'])) {
                if (!empty($build->getImageFileName())) {
                    $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/builds/' . $build->getImageRepository() . '/' . $build->getImageFileName();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $repo = date('Y/m');
                $fileName = uniqid() . '_build.jpg';
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/builds/' . $repo;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                file_put_contents($uploadDir . '/' . $fileName, base64_decode($data['imageBase64']));
                
                $build->setImageRepository($repo);
                $build->setImageFileName($fileName);
            }

            $success = $this->buildRepository->SqlUpdateBuild($build);

            if ($success) {
                $this->redirect('/builds/' . $id . '?success=updated');
            } else {
                $this->render('builds/edit.html.twig', [
                    'build' => $build,
                    'error' => 'Échec de la mise à jour du build.'
                ]);
            }
        }
    }

    //Delete a build
    public function deleteBuild(int $id): void {
        $userSession = $_SESSION['user'] ?? null;
        if (!$userSession) {
            $this->redirect('/login');
            return;
        }

        $build = $this->buildRepository->SqlGetBuildById($id);

        if (!$build) {
            $this->redirect('/builds');
            return;
        }

        if ($build->getAuthorId() !== $userSession['id'] && $userSession['role'] !== 'admin') {
            $this->redirect('/builds?error=unauthorized');
            return;
        }

        if (!empty($build->getImageFileName())) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/builds/' . $build->getImageRepository() . '/' . $build->getImageFileName();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->buildRepository->SqlDeleteBuild($id);

        $this->redirect('/builds?success=deleted');
    }

    //Download build as PDF
    public function downloadPdf(int $id): void {
        $build = $this->buildRepository->SqlGetBuildById($id);
        
        if (!$build) {
            $this->redirect('/builds');
            return;
        }

        $html = $this->twig->render('pdf/build_sheet.html.twig', [
            'build' => $build
        ]);

        $pdfService = new PdfService();
        $binaryPdf = $pdfService->generateBinaryPdf($html);

        //Naming the file with build name and date
        $safeName = str_replace([' ', "'", '"'], '-', $build->getName());
        $fileName = "Build_" . $safeName . "_" . date('Y-m-d') . ".pdf";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        
        echo $binaryPdf;
        exit;
    }

    //Report a build (send email with PDF)
    public function report(int $id): void {
        $build = $this->buildRepository->SqlGetBuildById($id);
        if (!$build) {
            $this->redirect('/builds');
            return;
        }

        $adminEmail = $_ENV['ADMIN_MAIL'] ?? 'default-admin@test.fr';

        $pdfService = new PdfService();
        $html = $this->twig->render('pdf/build_sheet.html.twig', ['build' => $build]);
        $pdfContent = $pdfService->generateBinaryPdf($html);

        try {
            $mailer = new MailerService();
            $mailer->sendReportWithPdf($adminEmail, $build->getName(), $pdfContent);
            
            $this->redirect('/builds/' . $id . '?success=reported');
        } catch (\Exception $e) {
            die("Erreur d'envoi mail : " . $e->getMessage());
        }
    }
}