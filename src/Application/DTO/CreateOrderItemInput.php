<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class CreateOrderItemInput
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
    ) {
    }
}