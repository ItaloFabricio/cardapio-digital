<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use RuntimeException;

/**
 * Lancada quando os dados de uma requisicao tem o formato errado.
 */
final class ValidationException extends RuntimeException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Os dados enviados são inválidos.');
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}