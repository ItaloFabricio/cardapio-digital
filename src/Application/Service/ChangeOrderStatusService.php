<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepositoryInterface;

/**
 * Caso de uso: "mudar o status de um pedido".
 */
final class ChangeOrderStatusService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function execute(int $orderId, OrderStatus $newStatus): Order
    {
        $order = $this->findOrderOrFail($orderId);

        $order->changeStatus($newStatus);

        $this->orderRepository->save($order);

        return $order;
    }

    private function findOrderOrFail(int $orderId): Order
    {
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw OrderNotFoundException::withId($orderId);
        }

        return $order;
    }
}