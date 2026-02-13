<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
header('Content-Type: application/json; charset=utf-8');

//Load dotenv file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();