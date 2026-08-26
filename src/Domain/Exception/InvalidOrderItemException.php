<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class InvalidOrderItemException extends DomainException
{
    public static function invalidQuantity(int $quantity): self
    {
        return new self(sprintf(
            'Quantidade inválida para um item de pedido: %d. Deve ser maior que zero.',
            $quantity,
        ));
    }
}