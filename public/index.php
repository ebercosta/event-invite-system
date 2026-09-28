<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Helpers\Router;
use App\Helpers\Auth;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

Auth::startSession();

$routes = require __DIR__ . '/../config/routes.php';
$router = new Router($routes);

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
