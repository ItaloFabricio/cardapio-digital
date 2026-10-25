<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\Money;

/**
 * DTO de saida do caso de uso "aplicar cupom": apenas carrega o
 * resultado do calculo para quem chamou o Service.
 */
final class ApplyCouponResult
{
    public function __construct(
        public readonly string $couponCode,
        public readonly Money $discount,
        public readonly Money $finalTotal,
    ) {
    }
}