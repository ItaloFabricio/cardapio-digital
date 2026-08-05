<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Exception de dominio da infraestrutura HTTP.
 *
 * Por que uma Exception propria em vez de lancar uma generica?
 * Porque no ponto onde capturamos ela (front controller), queremos
 * diferenciar "rota nao existe" (404) de qualquer outro erro inesperado
 * (500). Exceptions especificas viram controle de fluxo semantico.
 */
final class RouteNotFoundException extends \RuntimeException
{
    public static function forUri(string $method, string $uri): self
    {
        return new self(sprintf('Rota nao encontrada: %s %s', $method, $uri));
    }
}
