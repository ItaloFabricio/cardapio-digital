<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\ValueObject\Money;
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

    public static function notFound(string $code): self
    {
        return new self(sprintf('Cupom "%s" não encontrado.', $code));
    }

    public static function expired(string $code): self
    {
        return new self(sprintf('O cupom "%s" está expirado.', $code));
    }

    public static function minimumOrderNotReached(string $code, Money $minimum): self
    {
        return new self(sprintf(
            'O cupom "%s" exige um pedido mínimo de %s.',
            $code,
            $minimum->format(),
        ));
    }
}