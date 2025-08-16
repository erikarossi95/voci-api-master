<?php
// public/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dotenvPath = dirname(__DIR__) . '/.env';
if (file_exists($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}

define('APP_ROOT', dirname(__DIR__));

spl_autoload_register(function ($class) {
    $prefix = 'VociApi\\';
    $base_dir = APP_ROOT . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use VociApi\Core\Request;
use VociApi\Core\Response;
use VociApi\Core\Router;
use VociApi\Controllers\MediaTypeController;
use VociApi\Controllers\AuthorController;
use VociApi\Controllers\ContentController;


$request = new Request();
$response = new Response();

$router = new Router($request, $response);

$router->get('/media-types', [MediaTypeController::class, 'getAll']);
$router->get('/media-types/{id}', [MediaTypeController::class, 'getById']);
$router->post('/media-types', [MediaTypeController::class, 'create']);
$router->put('/media-types/{id}', [MediaTypeController::class, 'update']);
$router->delete('/media-types/{id}', [MediaTypeController::class, 'delete']);

$router->get('/authors', [AuthorController::class, 'getAll']);
$router->get('/authors/{id}', [AuthorController::class, 'getById']);
$router->post('/authors', [AuthorController::class, 'create']);
$router->put('/authors/{id}', [AuthorController::class, 'update']);
$router->delete('/authors/{id}', [AuthorController::class, 'delete']);

$router->get('/contents', [ContentController::class, 'getAll']);
$router->get('/contents/{id}', [ContentController::class, 'getById']);
$router->post('/contents', [ContentController::class, 'create']);
$router->put('/contents/{id}', [ContentController::class, 'update']);
$router->delete('/contents/{id}', [ContentController::class, 'delete']);


$router->resolve();
