<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\ApplyCouponResult;
use App\Application\Strategy\DiscountStrategyFactory;
use App\Domain\Entity\Coupon;
use App\Domain\Exception\InvalidCouponException;
use App\Domain\Repository\CouponRepositoryInterface;
use App\Domain\Specification\CouponNotExpiredSpecification;
use App\Domain\Specification\MinimumOrderValueSpecification;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;

final class ApplyCouponService
{
    public function __construct(
        private readonly CouponRepositoryInterface $couponRepository,
        private readonly DiscountStrategyFactory $strategyFactory,
        private readonly CouponNotExpiredSpecification $notExpired,
        private readonly MinimumOrderValueSpecification $minimumOrderValue,
    ) {
    }

    public function execute(
        string $code,
        Money $orderTotal,
        DateTimeImmutable $now,
    ): ApplyCouponResult {
        $coupon = $this->findCouponOrFail($code);

        $this->assertCouponIsEligible($coupon, $orderTotal, $now);

        $discount = $this->strategyFactory
            ->createFor($coupon)
            ->apply($orderTotal);

        return new ApplyCouponResult(
            couponCode: $coupon->code(),
            discount: $discount,
            finalTotal: $orderTotal->subtract($discount),
        );
    }

    private function findCouponOrFail(string $code): Coupon
    {
        $coupon = $this->couponRepository->findByCode($code);

        if ($coupon === null) {
            throw InvalidCouponException::notFound($code);
        }

        return $coupon;
    }

    private function assertCouponIsEligible(
        Coupon $coupon,
        Money $orderTotal,
        DateTimeImmutable $now,
    ): void {
        if (!$this->notExpired->isSatisfiedBy($coupon, $orderTotal, $now)) {
            throw InvalidCouponException::expired($coupon->code());
        }

        if (!$this->minimumOrderValue->isSatisfiedBy($coupon, $orderTotal, $now)) {
            throw InvalidCouponException::minimumOrderNotReached(
                $coupon->code(),
                $coupon->minimumOrderValue(),
            );
        }
    }
}