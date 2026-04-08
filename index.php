<?php

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use App\Application\Services\ContactService;
use App\Application\Validators\ContactValidator;
use App\Infrastructure\Repositories\ContactRepository;
use App\Presentation\Controller\ContactController;
use App\Presentation\Http\JsonResponse;
use App\Presentation\Http\Router;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo        = require __DIR__ . '/config/database.php';
    $repository = new ContactRepository($pdo);
    $validator      = new ContactValidator();
    $service        = new ContactService($repository, $validator);
    $controller     = new ContactController($service);

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath   = (PHP_SAPI !== 'cli-server') ? dirname($scriptName) : '';
    $basePath   = ($basePath === '\\' || $basePath === '/') ? '' : $basePath;

    $router = new Router($basePath);

    $router
        ->get('/contacts',         [$controller, 'index'])
        ->get('/contacts/{id}',    [$controller, 'show'])
        ->post('/contacts',        [$controller, 'store'])
        ->put('/contacts/{id}',    [$controller, 'update'])
        ->delete('/contacts/{id}', [$controller, 'destroy']);

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (\InvalidArgumentException $e) {
    $details = json_decode($e->getMessage(), true);
    JsonResponse::error('Error de validación.', 400, is_array($details) ? $details : [$e->getMessage()]);

} catch (\RuntimeException $e) {
    $code   = $e->getCode();
    $status = ($code >= 400 && $code < 600) ? $code : 500;
    JsonResponse::error($e->getMessage(), $status);

} catch (\Throwable $e) {
    JsonResponse::error('Error interno del servidor.', 500);
}
