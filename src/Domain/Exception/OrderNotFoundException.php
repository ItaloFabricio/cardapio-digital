<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

/**
 * Nenhum pedido existe com o id informado.
 */
final class OrderNotFoundException extends DomainException
{
    public static function withId(int $orderId): self
    {
        return new self(sprintf(
            'Pedido com id %d não foi encontrado.',
            $orderId,
        ));
    }
}