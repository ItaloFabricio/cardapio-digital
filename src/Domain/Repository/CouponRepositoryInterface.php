<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Coupon;

interface CouponRepositoryInterface
{
    
    public function save(Coupon $coupon): void;

    public function findByCode(string $code): ?Coupon;
}