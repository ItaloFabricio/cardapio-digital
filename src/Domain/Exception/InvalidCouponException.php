<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

/**
 * Violacoes de regras de negocio especificas da entidade Coupon.
 */
final class InvalidCouponException extends DomainException
{
    public static function emptyCode(): self
    {
        return new self('O código do cupom não pode ser vazio.');
    }

    public static function invalidDiscountValue(string $type, int $value): self
    {
        return new self(sprintf(
            'Valor de desconto inválido (%d) para o tipo "%s".',
            $value,
            $type,
        ));
    }

    public static function idAlreadyAssigned(int $id): self
    {
        return new self(sprintf(
            'Este cupom já possui um id atribuído: %d.',
            $id,
        ));
    }
}