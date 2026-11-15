<?php

declare(strict_types=1);

namespace App\Domain\Enum;

/**
 * Status possiveis de um pedido e as transicoes permitidas entre eles.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * Nome do status em portugues, para exibir a pessoas.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Confirmed => 'Confirmado',
            self::Preparing => 'Em preparo',
            self::Ready => 'Pronto',
            self::Delivered => 'Entregue',
            self::Cancelled => 'Cancelado',
        };
    }

    /**
     * Para quais status este pedido pode ir a partir do status atual.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Preparing, self::Cancelled],
            self::Preparing => [self::Ready],
            self::Ready => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Um status final nao aceita nenhuma mudanca posterior.
     */
    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
