<?php

declare(strict_types=1);

use App\Infrastructure\Config\EnvironmentLoader;
use App\Infrastructure\Config\TimezoneConfigurator;
use App\Infrastructure\Container\ServiceContainer;
use App\Infrastructure\Http\Router;
use App\Presentation\ApiRoutes;
use App\Presentation\ExceptionHandler;
use App\Presentation\HttpKernel;
use App\Presentation\Response\ResponseEmitter;

require dirname(__DIR__) . '/vendor/autoload.php';

$exceptionHandler = new ExceptionHandler();

try {
    // 1. Abrir o restaurante: ambiente, relogio e rotas.
    (new EnvironmentLoader())->load(dirname(__DIR__) . '/.env');
    (new TimezoneConfigurator())->configure($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

    $router = new Router();
    (new ApiRoutes(new ServiceContainer()))->register($router);

    // 2. Atender o cliente: texto cru entra, resposta sai.
    $response = (new HttpKernel($router, $exceptionHandler))->handle(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        (string) file_get_contents('php://input'),
    );
} catch (Throwable $exception) {
    // Falha ao abrir o restaurante (ex.: .env ilegivel, fuso invalido).
    $response = $exceptionHandler->handle($exception);
}

// 3. Entregar a resposta.
(new ResponseEmitter())->emit($response);