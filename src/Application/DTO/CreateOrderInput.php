<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class CreateOrderInput
{
    /**
     * @param list<CreateOrderItemInput> $items
     */
    public function __construct(
        public readonly ?int $customerId,
        public readonly array $items,
    ) {
    }
}