<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Coupon;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;

/**
 * Regra: o cupom so pode ser usado ate o instante de sua validade.
 */
final class CouponNotExpiredSpecification implements CouponSpecificationInterface
{
    public function isSatisfiedBy(
        Coupon $coupon,
        Money $orderTotal,
        DateTimeImmutable $now,
    ): bool {
        return $now <= $coupon->validUntil();
    }
}