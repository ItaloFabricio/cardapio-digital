<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Coupon;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;

/**
 * Regra: o total do pedido deve ser igual ou maior que o valor minimo
 * exigido pelo cupom.
 */
final class MinimumOrderValueSpecification implements CouponSpecificationInterface
{
    public function isSatisfiedBy(
        Coupon $coupon,
        Money $orderTotal,
        DateTimeImmutable $now,
    ): bool {
        return $orderTotal->isGreaterThanOrEqual($coupon->minimumOrderValue());
    }
}