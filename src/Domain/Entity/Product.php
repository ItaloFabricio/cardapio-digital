<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidProductException;
use App\Domain\ValueObject\Money;

final class Product
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private Money $price,
        private int $categoryId,
        private bool $available,
    ) {
        $this->guardName($this->name);
    }

    public static function create(
        string $name,
        string $description,
        Money $price,
        int $categoryId,
    ): self {
        return new self(
            id: null,
            name: $name,
            description: $description,
            price: $price,
            categoryId: $categoryId,
            available: true,
        );
    }

    public static function reconstruct(
        int $id,
        string $name,
        string $description,
        Money $price,
        int $categoryId,
        bool $available,
    ): self {
        return new self($id, $name, $description, $price, $categoryId, $available);
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw InvalidProductException::idAlreadyAssigned($this->id);
        }

        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function rename(string $name): void
    {
        $this->guardName($name);
        $this->name = $name;
    }

    public function changePrice(Money $price): void
    {
        $this->price = $price;
    }

    public function markAsUnavailable(): void
    {
        $this->available = false;
    }

    public function markAsAvailable(): void
    {
        $this->available = true;
    }

    private function guardName(string $name): void
    {
        if (trim($name) === '') {
            throw InvalidProductException::emptyName();
        }
    }
}