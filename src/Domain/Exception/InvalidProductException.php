<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;
final class InvalidProductException extends DomainException
{
    public static function emptyName(): self
    {
        return new self('O nome do produto não pode ser vazio.');
    }

    public static function idAlreadyAssigned(int $id): self
    {
        return new self(sprintf(
            'Este produto já possui um id atribuído: %d.',
            $id,
        ));
    }
}