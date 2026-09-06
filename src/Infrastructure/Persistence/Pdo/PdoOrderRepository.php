<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\OrderItem;
use PDO;
use Throwable;

final class PdoOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function save(Order $order): void
    {
        $this->connection->beginTransaction();

        try {
            if ($order->id() === null) {
                $this->insertOrder($order);
            }

            $this->replaceItems($order);

            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();

            throw $exception;
        }
    }

    public function findById(int $id): ?Order
    {
        $statement = $this->connection->prepare(
            'SELECT id, customer_id, status FROM orders WHERE id = :id',
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return Order::reconstruct(
            id: (int) $row['id'],
            customerId: $row['customer_id'] !== null ? (int) $row['customer_id'] : null,
            items: $this->findItemsByOrderId((int) $row['id']),
            status: (string) $row['status'],
        );
    }

    private function insertOrder(Order $order): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO orders (customer_id, status) VALUES (:customer_id, :status)',
        );

        $statement->execute([
            'customer_id' => $order->customerId(),
            'status' => $order->status(),
        ]);

        $order->assignId((int) $this->connection->lastInsertId());
    }

    private function replaceItems(Order $order): void
    {
        $deleteStatement = $this->connection->prepare(
            'DELETE FROM order_items WHERE order_id = :order_id',
        );
        $deleteStatement->execute(['order_id' => $order->id()]);

        $insertStatement = $this->connection->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, unit_price_in_cents, quantity)
             VALUES (:order_id, :product_id, :product_name, :unit_price_in_cents, :quantity)',
        );

        foreach ($order->items() as $item) {
            $insertStatement->execute([
                'order_id' => $order->id(),
                'product_id' => $item->productId(),
                'product_name' => $item->productName(),
                'unit_price_in_cents' => $item->unitPrice()->toCents(),
                'quantity' => $item->quantity(),
            ]);
        }
    }

    /**
     * @return list<OrderItem>
     */
    private function findItemsByOrderId(int $orderId): array
    {
        $statement = $this->connection->prepare(
            'SELECT product_id, product_name, unit_price_in_cents, quantity
             FROM order_items
             WHERE order_id = :order_id',
        );
        $statement->execute(['order_id' => $orderId]);

        $items = [];

        foreach ($statement->fetchAll() as $row) {
            $items[] = OrderItem::create(
                productId: (int) $row['product_id'],
                productName: (string) $row['product_name'],
                unitPrice: Money::fromCents((int) $row['unit_price_in_cents']),
                quantity: (int) $row['quantity'],
            );
        }

        return $items;
    }
}