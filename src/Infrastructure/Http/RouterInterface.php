<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Contrato do roteador HTTP.
 */
interface RouterInterface
{
    public function get(string $path, callable $handler): void;

    public function post(string $path, callable $handler): void;

    /**
     * Resolve a rota atual e executa o handler correspondente.
     *
     * @param array<mixed> $extraArguments repassados ao handler ANTES dos
     *                                     parametros extraidos da URL
     */
    public function dispatch(string $method, string $uri, array $extraArguments = []): mixed;
}