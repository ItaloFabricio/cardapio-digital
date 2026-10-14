<?php

declare(strict_types=1);

namespace App\Application\Strategy;

use App\Domain\Exception\InvalidDiscountException;
use App\Domain\ValueObject\Money;

/**
 * Desconto de X% sobre o total do pedido.
 */
final class PercentageDiscountStrategy implements DiscountStrategyInterface
{
    public function __construct(
        private readonly float $percentage,
    ) {
        if ($percentage <= 0 || $percentage > 100) {
            throw InvalidDiscountException::invalidPercentage($percentage);
        }
    }

    public function apply(Money $orderTotal): Money
    {
        return $orderTotal->multiply($this->percentage / 100);
    }
}