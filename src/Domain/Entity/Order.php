<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\InvalidOrderException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\OrderItem;

/**
 * Order e uma Entity que reune varios OrderItem (Value Objects).
 *
 * O status e controlado por uma maquina de estados (OrderStatus): ele
 * so muda atraves de changeStatus(), que recusa transicoes invalidas.
 */
final class Order
{
    /**
     * @param list<OrderItem> $items
     */
    private function __construct(
        private ?int $id,
        private readonly ?int $customerId,
        private array $items,
        private OrderStatus $status,
    ) {
    }

    public static function create(?int $customerId = null): self
    {
        return new self(
            id: null,
            customerId: $customerId,
            items: [],
            status: OrderStatus::Pending,
        );
    }

    /**
     * Uso: quando um pedido existente esta sendo recarregado do banco.
     *
     * @param list<OrderItem> $items
     */
    public static function reconstruct(
        int $id,
        ?int $customerId,
        array $items,
        OrderStatus $status,
    ): self {
        return new self($id, $customerId, $items, $status);
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw InvalidOrderException::idAlreadyAssigned($this->id);
        }

        $this->id = $id;
    }

    public function addItem(OrderItem $item): void
    {
        if ($this->status !== OrderStatus::Pending) {
            throw InvalidOrderException::cannotModifyConfirmedOrder();
        }

        $this->items[] = $item;
    }

    public function changeStatus(OrderStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw InvalidOrderException::invalidStatusTransition($this->status, $newStatus);
        }

        if ($newStatus === OrderStatus::Confirmed && $this->items === []) {
            throw InvalidOrderException::cannotConfirmEmptyOrder();
        }

        $this->status = $newStatus;
    }

    public function total(): Money
    {
        $total = Money::zero();

        foreach ($this->items as $item) {
            $total = $total->add($item->subtotal());
        }

        return $total;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function customerId(): ?int
    {
        return $this->customerId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    /**
     * @return list<OrderItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function itemCount(): int
    {
        return count($this->items);
    }
}