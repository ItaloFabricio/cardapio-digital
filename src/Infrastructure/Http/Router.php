<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Router HTTP minimalista, construido para fins de estudo.
 *
 * Conceitos de POO demonstrados aqui:
 * - Encapsulamento: a lista de rotas ($routes) e privada; ninguem de fora
 *   manipula o array diretamente, so atraves de get()/post().
 * - Composicao: o Router "tem" uma colecao de Route (composicao, nao heranca -
 *   nao faz sentido Router "ser um tipo de" Route).
 * - match(): usado no lugar de uma cadeia de if/elseif para comparar o
 *   metodo HTTP, deixando o codigo mais declarativo.
 *
 * Suporta parametros dinamicos no formato /produtos/{id}.
 */
final class Router implements RouterInterface
{
    /** @var list<Route> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = new Route($method, $path, $handler);
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if (!$route->matchesMethod($method)) {
                continue;
            }

            $params = $route->matchPath($path);

            if ($params !== null) {
                return ($route->handler)(...$params);
            }
        }

        throw RouteNotFoundException::forUri($method, $path);
    }

    private function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        return rtrim($path, '/') ?: '/';
    }
}
