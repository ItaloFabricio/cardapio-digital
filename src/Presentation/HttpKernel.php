<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Infrastructure\Http\RouterInterface;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;
use LogicException;
use Throwable;

/**
 * Transforma uma requisicao (metodo, endereco e corpo, em texto) em uma
 * resposta JsonResponse.
 */
final class HttpKernel
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ExceptionHandler $exceptionHandler,
    ) {
    }

    public function handle(string $method, string $uri, string $rawBody): JsonResponse
    {
        try {
            $request = HttpRequest::fromRaw($method, $uri, $rawBody);

            $response = $this->router->dispatch(
                $request->method(),
                $request->path(),
                [$request],
            );

            if (!$response instanceof JsonResponse) {
                throw new LogicException('Todo handler de rota deve retornar um JsonResponse.');
            }

            return $response;
        } catch (Throwable $exception) {
            return $this->exceptionHandler->handle($exception);
        }
    }
}