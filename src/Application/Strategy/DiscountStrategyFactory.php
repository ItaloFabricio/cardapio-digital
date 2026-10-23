<?php

declare(strict_types=1);

namespace App\Application\Strategy;

use App\Domain\Entity\Coupon;
use App\Domain\Enum\DiscountType;
use App\Domain\ValueObject\Money;

/**
 * Factory: monta a estrategia de desconto correta para um cupom.
 */
final class DiscountStrategyFactory
{
    public function createFor(Coupon $coupon): DiscountStrategyInterface
    {
        return match ($coupon->discountType()) {
            DiscountType::Percentage => new PercentageDiscountStrategy(
                $coupon->discountValue(),
            ),
            DiscountType::Fixed => new FixedDiscountStrategy(
                Money::fromCents($coupon->discountValue()),
            ),
        };
    }
}