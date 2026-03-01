<?php

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Diablo\Core\Request;
use Diablo\Data\DataBaseConnection;
use Diablo\Repository\BuildRepository;
use Diablo\Repository\UserRepository;
use Diablo\Service\JwtService;
use Diablo\Security\JwtMiddleware;
use Diablo\Service\AuthService;
use Diablo\Controller\Api\AuthController;
use Diablo\Controller\Api\BuildController;
use Diablo\Controller\Api\UserController;
use Diablo\Controller\Api\AdminController;

//Load dotenv file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

//Initialize session
session_start();
//Initialize request
$request = new Request();

//Initialize database connection
try {
    $pdo = DataBaseConnection::getInstance();
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

//Initialize repositories
$buildRepository = new BuildRepository($pdo);
$userRepository = new UserRepository($pdo);

//Initialize services
//JWT service
$jwtService = new JwtService(
    $_ENV['JWT_SECRET'] ?? 'dev_secret',
    'diablo.local',
    3600
);
//Authentication service and middleware
$authService = new AuthService($userRepository,$jwtService);
$jwtMiddleware = new JwtMiddleware($jwtService);

//Minimal routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Route for home page
if (($uri === '/' || $uri === '') && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\HomeController($request, $buildRepository);
    $controller->index();
    exit;
}

//--- ADMIN ROUTES ---
//Admin dashboard
if ($uri === '/admin/dashboard' && $method === 'GET') {
    $controller = new \Diablo\Controller\Admin\DashboardController($request, $buildRepository, $userRepository);
    $controller->index();
    exit;
}

//Admin user management
if ($uri === '/admin/users' && $method === 'GET') {
    $controller = new \Diablo\Controller\Admin\UserController($request, $userRepository);
    $controller->index();
    exit;
}

if ($uri === '/admin/builds' && $method === 'GET') {
    $controller = new \Diablo\Controller\Admin\BuildController($request, $buildRepository);
    $controller->index();
    exit;
}

if (preg_match('#^/admin/users/delete/(\d+)$#', $uri, $matches) && $method === 'POST') {
    $controller = new \Diablo\Controller\Admin\UserController($request, $userRepository);
    $controller->deleteUser((int)$matches[1]);
    exit;
}

if (preg_match('#^/admin/builds/delete/(\d+)$#', $uri, $matches) && $method === 'POST') {
    $controller = new \Diablo\Controller\Admin\BuildController($request, $buildRepository);
    $controller->deleteBuild((int)$matches[1]);
    exit;
}

//--- PUBLIC ROUTES ---
//Register page
if ($uri === '/register' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->register();
    exit;
}
if ($uri === '/register' && $method === 'POST') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->register();
    exit;
}

//Login page
if ($uri === '/login' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->loginForm();
    exit;
}

//Login form treatment
if ($uri === '/login' && $method === 'POST') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->login();
    exit;
}

//Logout route
if ($uri === '/logout' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->logout();
    exit;
}

//GetAll builds
if ($uri === '/builds' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository, $userRepository);
    $controller->index();
    exit;
}

//Route for build details page
if (preg_match('#^/builds/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository, $userRepository);
    $controller->show((int)$matches[1]);
    exit;
}

//Route for user details page
if (preg_match('#^/user/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->showProfile((int)$matches[1]);
    exit;
}

//--- USER ROUTES ---
//User profile page
if ($uri === '/profile' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->profile();
    exit;
}

//Create a build GET and POST routes
if ($uri === '/builds/create' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository, $userRepository);
    $controller->create(); 
    exit;
}
if ($uri === '/builds/create' && $method === 'POST') {
    $auth = $_SESSION['user'] ?? null; //take user session to associate it with the build
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository, $userRepository);
    $controller->store();
    exit;
}

//Update profile route
if ($uri === '/profile/edit' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->editProfile();
    exit;
}
if ($uri === '/profile/update' && $method === 'POST') {
    $controller = new \Diablo\Controller\Web\AuthController($request, $authService, $userRepository);
    $controller->updateProfile();
    exit;
}

//Download build PDF
if (preg_match('#^/builds/download/(\d+)$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository, $userRepository);
    $controller->downloadPdf($id);
    exit;
}

//Report a build
if (preg_match('#^/builds/report/(\d+)$#', $uri, $matches) && $method === 'POST') {
    $id = (int)$matches[1];
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository, $userRepository);
    $controller->report($id);
    exit;
}

//--- API ROUTES ---
//Register route
if ($uri === '/api/register' && $method === 'POST') {
    $controller = new UserController($request, $userRepository);
    $controller->register();
    exit;
}

//Login route
if ($uri === '/api/login' && $method === 'POST') {

    $controller = new AuthController($request, $authService);
    $controller->login();

    exit;
}

//Profile of connected user - the "me" route
if ($uri === '/api/profile' && $method === 'GET') {
    $auth = $jwtMiddleware->requireAuth();
    $controller = new UserController($request, $userRepository);
    $controller->profile($auth);
    exit;
}
if ($uri === '/api/profile' && ($method === 'PUT' || $method === 'PATCH')) {
    $auth = $jwtMiddleware->requireAuth();
    $controller = new UserController($request, $userRepository);
    $controller->update($auth);
    exit;
}

//Dynamic routes - ID extraction
$parts = explode('/', $uri);
$resourceId = isset($parts[3]) && is_numeric($parts[3]) ? (int)$parts[3] : null;

//Route - DELETE : /api/builds/{id}
if (strpos($uri, '/api/builds/') === 0 && $method === 'DELETE' && $resourceId) {
    $auth = $jwtMiddleware->requireAuth();
    $controller = new BuildController($request, $buildRepository, $auth);
    $controller->delete($resourceId);
    exit;
}

//Route - UPDATE : /api/builds/{id}
if (strpos($uri, '/api/builds/') === 0 && ($method === 'PUT' || $method === 'PATCH') && $resourceId) {
    $auth = $jwtMiddleware->requireAuth();
    $controller = new BuildController($request, $buildRepository, $auth);
    $controller->update($resourceId);
    exit;
}

//Create build protected route
if ($uri === '/api/builds' && $method === 'POST') {
    $auth = $jwtMiddleware->requireAuth();

    $controller = new BuildController(
        $request,
        $buildRepository,
        $auth
    );

    $controller->create();
    exit;
}

//List all builds
if ($uri === '/api/builds' && $method === 'GET') {

    $controller = new BuildController(
        $request,
        $buildRepository,
        []
    );

    $controller->index();
    exit;
}

//--- ADMIN API ROUTES ---
//Delete build (Admin)
//Route: DELETE /api/admin/builds/{id}
if (strpos($uri, '/api/admin/builds/') === 0 && $method === 'DELETE') {
    $resourceId = (int)basename($uri);
    $auth = $jwtMiddleware->requireAuth(); // Le middleware vérifie le token
    
    $controller = new AdminController($request, $buildRepository, $userRepository, $auth);
    $controller->deleteBuild($resourceId);
    exit;
}

//Delete user (Admin)
//Route: DELETE /api/admin/users/{id}
if (strpos($uri, '/api/admin/users/') === 0 && $method === 'DELETE') {
    $resourceId = (int)basename($uri);
    $auth = $jwtMiddleware->requireAuth();
    
    $controller = new AdminController($request, $buildRepository, $userRepository, $auth);
    $controller->deleteUser($resourceId);
    exit;
}

//Not found route
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Route not found'
]);