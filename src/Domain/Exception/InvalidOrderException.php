<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class InvalidOrderException extends DomainException
{
    public static function idAlreadyAssigned(int $id): self
    {
        return new self(sprintf(
            'Este pedido já possui um id atribuído: %d.',
            $id,
        ));
    }

    public static function cannotModifyConfirmedOrder(): self
    {
        return new self(
            'Não é possível alterar itens de um pedido que já não está mais pendente.',
        );
    }

    public static function productNotFound(int $productId): self
    {
        return new self(sprintf(
            'Produto com id %d não foi encontrado.',
            $productId,
        ));
    }
 
    public static function productUnavailable(int $productId, string $productName): self
    {
        return new self(sprintf(
            'O produto "%s" (id %d) não está disponível no momento.',
            $productName,
            $productId,
        ));
    }
}