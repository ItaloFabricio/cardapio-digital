<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

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

    public function dispatch(string $method, string $uri, array $extraArguments = []): mixed
    {
        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if (!$route->matchesMethod($method)) {
                continue;
            }

            $params = $route->matchPath($path);

            if ($params !== null) {
                return ($route->handler)(...$extraArguments, ...$params);
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