<?php

declare(strict_types=1);

use App\Infrastructure\Http\Router;
use App\Infrastructure\Http\RouteNotFoundException;

require dirname(__DIR__) . '/vendor/autoload.php';

// Carrega variaveis de ambiente de forma simples (sem dependencia externa
// nesta fase). Na Fase 2, quando conectarmos ao banco de fato, avaliamos
// se vale a pena introduzir vlucas/phpdotenv.
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

header('Content-Type: application/json; charset=utf-8');

$router = new Router();

$router->get('/', function (): array {
    return [
        'projeto' => 'Cardapio Digital Inteligente',
        'status' => 'Fase 0 - fundacao do projeto concluida',
    ];
});

$router->get('/health', function (): array {
    return ['status' => 'ok'];
});

try {
    $resultado = $router->dispatch(
        method: $_SERVER['REQUEST_METHOD'],
        uri: $_SERVER['REQUEST_URI'],
    );

    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (RouteNotFoundException) {
    http_response_code(404);
    echo json_encode(['erro' => 'Recurso nao encontrado.'], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $exception) {
    // Nunca expomos $exception->getMessage() de erros inesperados ao
    // cliente - isso vazaria detalhes internos (Fase 7 vai formalizar
    // isso com um ErrorHandler dedicado).
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno do servidor.'], JSON_UNESCAPED_UNICODE);
}
