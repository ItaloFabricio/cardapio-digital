<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidOrderItemException;

final class OrderItem
{
    private function __construct(
        private readonly int $productId,
        private readonly string $productName,
        private readonly Money $unitPrice,
        private readonly int $quantity,
    ) {
        if ($quantity <= 0) {
            throw InvalidOrderItemException::invalidQuantity($quantity);
        }
    }

    public static function create(
        int $productId,
        string $productName,
        Money $unitPrice,
        int $quantity,
    ): self {
        return new self($productId, $productName, $unitPrice, $quantity);
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function subtotal(): Money
    {
        return $this->unitPrice->multiply((float) $this->quantity);
    }

    public function equals(self $other): bool
    {
        return $this->productId === $other->productId
            && $this->unitPrice->equals($other->unitPrice)
            && $this->quantity === $other->quantity;
    }
}