<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Presentation\Request\HttpRequest;
use App\Presentation\Request\InvalidRequestBodyException;

$scenarios = [
    ['GET com query string e sem corpo', 'GET', '/products?category=1', ''],
    ['POST (digitado em minúsculas) com corpo JSON', 'post', '/orders', '{"customerId": null, "items": [{"productId": 1, "quantity": 2}]}'],
    ['JSON quebrado', 'POST', '/orders', '{"items": ['],
    ['JSON que é um número, não um objeto', 'POST', '/orders', '123'],
    ['JSON que é uma lista, não um objeto', 'POST', '/orders', '[1, 2]'],
    ['Corpo vazio (só espaços)', 'POST', '/orders', '   '],
];

foreach ($scenarios as [$label, $method, $uri, $rawBody]) {
    echo $label . "\n";

    try {
        $request = HttpRequest::fromRaw($method, $uri, $rawBody);

        echo sprintf(
            "   método: %s | caminho: %s | corpo: %s\n\n",
            $request->method(),
            $request->path(),
            json_encode($request->body(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        );
    } catch (InvalidRequestBodyException $exception) {
        echo '   RECUSADO -> ' . $exception->getMessage() . "\n\n";
    }
}