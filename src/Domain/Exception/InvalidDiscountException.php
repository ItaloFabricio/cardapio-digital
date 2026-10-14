<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

/**
 * Violacoes de regras de negocio relacionadas a configuracao de descontos.
 */
final class InvalidDiscountException extends DomainException
{
    public static function invalidPercentage(float $percentage): self
    {
        return new self(sprintf(
            'Percentual de desconto inválido: %.2f. Deve estar entre 0 e 100.',
            $percentage,
        ));
    }
}