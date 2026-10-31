<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Infrastructure\Http\Router;
use App\Presentation\HttpKernel;
use App\Presentation\Response\JsonResponse;

// Um Router so para o teste, com uma rota que funciona e uma que quebra.
$router = new Router();

$router->get('/ok', static fn (): JsonResponse => JsonResponse::ok(['status' => 'ok']));

$router->get('/boom', static function (): JsonResponse {
    throw new RuntimeException('detalhe interno secreto');
});

$kernel = new HttpKernel($router);

$requests = [
    ['GET /ok', 'GET', '/ok', ''],
    ['GET /nao-existe', 'GET', '/nao-existe', ''],
    ['POST /ok com corpo quebrado', 'POST', '/ok', '{invalido'],
    ['GET /boom (erro inesperado)', 'GET', '/boom', ''],
];

foreach ($requests as [$label, $method, $uri, $rawBody]) {
    echo $label . "\n";

    $response = $kernel->handle($method, $uri, $rawBody);

    echo sprintf(
        "   status: %d %s | corpo: %s\n\n",
        $response->status()->value,
        $response->status()->name,
        $response->toJson(),
    );
}