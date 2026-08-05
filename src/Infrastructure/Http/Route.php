<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Representa uma unica rota registrada.
 *
 * E uma classe "burra" de proposito (baixa complexidade): ela sabe
 * comparar metodo e testar/extrair parametros do path. Nao sabe nada
 * sobre a lista de rotas nem sobre dispatch - essa responsabilidade
 * e do Router. Isso e SRP aplicado em uma classe pequena.
 */
final class Route
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        public readonly mixed $handler,
    ) {
    }

    public function matchesMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    /**
     * Retorna a lista de parametros extraidos do path, ou null se nao bater.
     * Ex.: path da rota "/produtos/{id}" com uri "/produtos/42" -> ["42"]
     *
     * @return list<string>|null
     */
    public function matchPath(string $uri): ?array
    {
        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $this->path);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches) !== 1) {
            return null;
        }

        array_shift($matches);

        return array_values($matches);
    }
}
