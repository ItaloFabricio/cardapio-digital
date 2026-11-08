<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Exception\InvalidOrderException;
use App\Domain\Exception\OrderNotFoundException;
use App\Infrastructure\Http\RouteNotFoundException;
use App\Presentation\ExceptionHandler;
use App\Presentation\Request\InvalidRequestBodyException;
use App\Presentation\Request\ValidationException;

$handler = new ExceptionHandler();

$scenarios = [
    'Corpo de requisição quebrado' => InvalidRequestBodyException::malformedJson(),
    'Erro de validação com detalhes' => new ValidationException([
        'items' => 'deve ser uma lista com ao menos um item.',
    ]),
    'Rota inexistente (com texto suspeito no endereço)' => RouteNotFoundException::forUri('GET', '/segredo<script>'),
    'Pedido não encontrado' => OrderNotFoundException::withId(42),
    'Regra de negócio violada' => InvalidOrderException::cannotConfirmEmptyOrder(),
    'Erro inesperado (com dado sensível na mensagem)' => new RuntimeException('senha do banco: 123456'),
    'LogicException comum (não é regra de negócio)' => new LogicException('falha de programação'),
];

$number = 1;

foreach ($scenarios as $label => $exception) {
    $response = $handler->handle($exception);

    echo "{$number}) {$label}\n";
    echo "   status: {$response->status()->value} {$response->status()->name} | corpo: {$response->toJson()}\n\n";

    $number++;
}