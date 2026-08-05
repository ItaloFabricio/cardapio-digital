<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Contrato do roteador HTTP.
 *
 * DIP em acao: o front controller (public/index.php) vai depender desta
 * interface, nao da classe concreta Router. Isso significa que, no futuro,
 * se voce decidir trocar por nikic/fast-route, basta criar uma nova classe
 * que implemente RouterInterface - nada no resto do sistema muda.
 */
interface RouterInterface
{
    public function get(string $path, callable $handler): void;

    public function post(string $path, callable $handler): void;

    /**
     * Resolve a rota atual e executa o handler correspondente.
     */
    public function dispatch(string $method, string $uri): mixed;
}
