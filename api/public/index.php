<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Diablo\Data\DataBaseConnection;
use Diablo\Repository\BuildRepository;
use Diablo\Repository\UserRepository;
header('Content-Type: application/json; charset=utf-8');

//Load dotenv file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

//Initialize database connection
try {
    $pdo = DataBaseConnection::getInstance();
} catch (Throwable $error) {//FIX ME : modif with json response body
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $error->getMessage()]);
    exit;
}

//Initialize repositories
$buildRepository = new BuildRepository($pdo);
$userRepository = new UserRepository($pdo);