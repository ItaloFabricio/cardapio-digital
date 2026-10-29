<?php

declare(strict_types=1);

use App\Infrastructure\Http\RouteNotFoundException;
use App\Infrastructure\Http\Router;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Request\InvalidRequestBodyException;
use App\Presentation\Response\HttpStatus;
use App\Presentation\Response\JsonResponse;
use App\Presentation\Response\ResponseEmitter;

require dirname(__DIR__) . '/vendor/autoload.php';

// Carrega variaveis de ambiente de forma simples (sem dependencia externa).
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

$router = new Router();

$router->get('/', static fn (): JsonResponse => JsonResponse::ok([
    'name' => 'Cardápio Digital Inteligente',
    'status' => 'online',
]));

$router->get('/health', static fn (): JsonResponse => JsonResponse::ok([
    'status' => 'ok',
]));

try {
    $request = HttpRequest::fromGlobals();

    $response = $router->dispatch($request->method(), $request->path());

    if (!$response instanceof JsonResponse) {
        throw new LogicException('Todo handler de rota deve retornar um JsonResponse.');
    }
} catch (InvalidRequestBodyException $exception) {
    $response = JsonResponse::error(HttpStatus::BadRequest, $exception->getMessage());
} catch (RouteNotFoundException) {
    $response = JsonResponse::error(HttpStatus::NotFound, 'Recurso não encontrado.');
} catch (Throwable $exception) {
    // O detalhe tecnico vai para o log interno, nunca para o cliente.
    error_log((string) $exception);

    $response = JsonResponse::error(HttpStatus::InternalServerError, 'Erro interno do servidor.');
}

(new ResponseEmitter())->emit($response);