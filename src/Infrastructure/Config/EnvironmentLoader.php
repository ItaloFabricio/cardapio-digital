<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

use RuntimeException;

/**
 * Carrega as variaveis de um arquivo .env para $_ENV.
 */
final class EnvironmentLoader
{
    public function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $values = parse_ini_file($path);

        if ($values === false) {
            throw new RuntimeException('Não foi possível ler o arquivo de ambiente (.env).');
        }

        foreach ($values as $key => $value) {
            $_ENV[$key] = $value;
        }
    }
}