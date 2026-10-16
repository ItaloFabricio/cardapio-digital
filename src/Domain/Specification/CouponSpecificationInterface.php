<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Coupon;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;

/**
 * Contrato do Specification Pattern para regras de elegibilidade de cupom.
 */
interface CouponSpecificationInterface
{
    public function isSatisfiedBy(
        Coupon $coupon,
        Money $orderTotal,
        DateTimeImmutable $now,
    ): bool;
}