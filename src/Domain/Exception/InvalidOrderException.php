<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Enum\OrderStatus;
use DomainException;

/**
 * Violacoes de regras de negocio especificas da entidade Order.
 */
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

    public static function invalidStatusTransition(OrderStatus $from, OrderStatus $to): self
    {
        return new self(sprintf(
            'Não é possível mudar o pedido de "%s" para "%s".',
            $from->value,
            $to->value,
        ));
    }

    public static function cannotConfirmEmptyOrder(): self
    {
        return new self('Não é possível confirmar um pedido sem itens.');
    }

    public static function orderNotFound(int $orderId): self
    {
        return new self(sprintf(
            'Pedido com id %d não foi encontrado.',
            $orderId,
        ));
    }
}