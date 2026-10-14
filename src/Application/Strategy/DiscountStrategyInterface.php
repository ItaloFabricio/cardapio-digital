<?php

declare(strict_types=1);

namespace App\Application\Strategy;

use App\Domain\ValueObject\Money;

/**
 * Contrato do Strategy Pattern para calculo de desconto.
 */
interface DiscountStrategyInterface
{
    public function apply(Money $orderTotal): Money;
}