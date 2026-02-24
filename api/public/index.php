<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Diablo\Core\Request;
use Diablo\Data\DataBaseConnection;
use Diablo\Repository\BuildRepository;
use Diablo\Repository\UserRepository;
use Diablo\Service\JwtService;
use Diablo\Service\AuthService;
use Diablo\Controller\Api\AuthController;
header('Content-Type: application/json; charset=utf-8');

//Load dotenv file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
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
$jwtService = new JwtService(
    $_ENV['JWT_SECRET'] ?? 'dev_secret',
    'diablo.local',
    3600
);

$authService = new AuthService(
    $userRepository,
    $jwtService
);

//Minimal routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

//Example route: login
if ($uri === '/api/login' && $method === 'POST') {

    $controller = new AuthController($request, $authService);
    $controller->login();

    exit;
}

//Not found route
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Route not found'
]);