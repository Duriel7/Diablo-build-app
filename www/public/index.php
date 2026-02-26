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

header('Content-Type: application/json; charset=utf-8');

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
} catch (Throwable $error) {//FIX ME : modif with json response body
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

//Health check
if ($uri === '' && $method === 'GET') {
    echo json_encode(['success' => true, 'message' => 'API OK']);
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
if ($uri === '/api/me' && $method === 'GET') {
    $auth = $jwtMiddleware->requireAuth(); // Vérifie le token
    $controller = new UserController($request, $userRepository);
    $controller->profile($auth);
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

//--- PUBLIC ROUTES ---
// Route for home page
if (($uri === '/' || $uri === '') && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\HomeController($request, $buildRepository);
    $controller->index();
    exit;
}

//GetAll builds
if ($uri === '/builds' && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository);
    $controller->index();
    exit;
}

//Route for build details page
if (preg_match('#^/builds/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $controller = new \Diablo\Controller\Web\BuildController($request, $buildRepository);
    $controller->show((int)$matches[1]);
    exit;
}

//Not found route
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Route not found'
]);