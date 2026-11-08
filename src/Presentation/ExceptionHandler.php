<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Domain\Exception\OrderNotFoundException;
use App\Infrastructure\Http\RouteNotFoundException;
use App\Presentation\Request\InvalidRequestBodyException;
use App\Presentation\Request\ValidationException;
use App\Presentation\Response\HttpStatus;
use App\Presentation\Response\JsonResponse;
use DomainException;
use Throwable;

/**
 * Transforma qualquer excecao em uma resposta HTTP.
 */
final class ExceptionHandler
{
    public function handle(Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof ValidationException => JsonResponse::validationFailed(
                $exception->getMessage(),
                $exception->errors(),
            ),
            // Mensagem fixa: a original repete o endereco digitado pelo usuario.
            $exception instanceof RouteNotFoundException => JsonResponse::error(
                HttpStatus::NotFound,
                'Recurso não encontrado.',
            ),
            $exception instanceof InvalidRequestBodyException => JsonResponse::error(
                HttpStatus::BadRequest,
                $exception->getMessage(),
            ),
            $exception instanceof OrderNotFoundException => JsonResponse::error(
                HttpStatus::NotFound,
                $exception->getMessage(),
            ),
            $exception instanceof DomainException => JsonResponse::error(
                HttpStatus::UnprocessableEntity,
                $exception->getMessage(),
            ),
            default => $this->unexpected($exception),
        };
    }

    private function unexpected(Throwable $exception): JsonResponse
    {
        // O detalhe tecnico vai para o log interno, nunca para o cliente.
        error_log((string) $exception);

        return JsonResponse::error(HttpStatus::InternalServerError, 'Erro interno do servidor.');
    }
}