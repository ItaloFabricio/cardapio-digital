<?php

declare(strict_types=1);

namespace App\Application\Strategy;

use App\Domain\ValueObject\Money;

/**
 * Desconto de um valor fixo em reais, nunca maior que o proprio total
 * do pedido (evita gerar total negativo).
 */
final class FixedDiscountStrategy implements DiscountStrategyInterface
{
    public function __construct(
        private readonly Money $amount,
    ) {
    }

    public function apply(Money $orderTotal): Money
    {
        if ($this->amount->isGreaterThan($orderTotal)) {
            return $orderTotal;
        }

        return $this->amount;
    }
}