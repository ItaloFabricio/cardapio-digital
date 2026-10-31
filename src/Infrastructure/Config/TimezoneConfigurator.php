<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

use InvalidArgumentException;

/**
 * Define o fuso horario padrao do PHP para toda a aplicacao.
 */
final class TimezoneConfigurator
{
    public function configure(string $timezone): void
    {
        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException(sprintf(
                'Fuso horário inválido: "%s".',
                $timezone,
            ));
        }

        date_default_timezone_set($timezone);
    }
}