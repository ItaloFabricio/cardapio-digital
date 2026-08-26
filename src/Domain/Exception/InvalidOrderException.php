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
}