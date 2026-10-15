<?php

declare(strict_types=1);

namespace App\Domain\Enum;

/**
 * Tipos de desconto que um cupom pode ter.
 */
enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
}