<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use RuntimeException;

/**
 * Lancada quando o corpo de uma requisicao nao pode ser entendido.
 */
final class InvalidRequestBodyException extends RuntimeException
{
    public static function malformedJson(): self
    {
        return new self('O corpo da requisição não é um JSON válido.');
    }

    public static function notAnObject(): self
    {
        return new self('O corpo da requisição deve ser um objeto JSON.');
    }
}