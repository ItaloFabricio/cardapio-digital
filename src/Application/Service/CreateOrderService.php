<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\CreateOrderInput;
use App\Application\DTO\CreateOrderItemInput;
use App\Domain\Entity\Order;
use App\Domain\Exception\InvalidOrderException;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\OrderItem;

final class CreateOrderService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function execute(CreateOrderInput $input): Order
    {
        $order = Order::create($input->customerId);

        foreach ($input->items as $itemInput) {
            $order->addItem($this->buildOrderItem($itemInput));
        }

        $this->orderRepository->save($order);

        return $order;
    }

    private function buildOrderItem(CreateOrderItemInput $itemInput): OrderItem
    {
        $product = $this->productRepository->findById($itemInput->productId);

        if ($product === null) {
            throw InvalidOrderException::productNotFound($itemInput->productId);
        }

        if (!$product->isAvailable()) {
            throw InvalidOrderException::productUnavailable($product->id(), $product->name());
        }

        return OrderItem::create(
            productId: $product->id(),
            productName: $product->name(),
            unitPrice: $product->price(),
            quantity: $itemInput->quantity,
        );
    }
}