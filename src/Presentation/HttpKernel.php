<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Infrastructure\Http\RouteNotFoundException;
use App\Infrastructure\Http\RouterInterface;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Request\InvalidRequestBodyException;
use App\Presentation\Response\HttpStatus;
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
    ) {
    }

    public function handle(string $method, string $uri, string $rawBody): JsonResponse
    {
        try {
            $request = HttpRequest::fromRaw($method, $uri, $rawBody);

            $response = $this->router->dispatch($request->method(), $request->path());

            if (!$response instanceof JsonResponse) {
                throw new LogicException('Todo handler de rota deve retornar um JsonResponse.');
            }

            return $response;
        } catch (InvalidRequestBodyException $exception) {
            return JsonResponse::error(HttpStatus::BadRequest, $exception->getMessage());
        } catch (RouteNotFoundException) {
            return JsonResponse::error(HttpStatus::NotFound, 'Recurso não encontrado.');
        } catch (Throwable $exception) {
            // O detalhe tecnico vai para o log interno, nunca para o cliente.
            error_log((string) $exception);

            return JsonResponse::error(HttpStatus::InternalServerError, 'Erro interno do servidor.');
        }
    }
}