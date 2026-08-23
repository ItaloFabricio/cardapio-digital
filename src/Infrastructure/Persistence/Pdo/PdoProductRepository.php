<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\Money;
use PDO;

final class PdoProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function save(Product $product): void
    {
        if ($product->id() === null) {
            $this->insert($product);

            return;
        }

        $this->update($product);
    }

    public function findById(int $id): ?Product
    {
        $statement = $this->connection->prepare(
            'SELECT id, category_id, name, description, price_in_cents, available
             FROM products
             WHERE id = :id',
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @return list<Product>
     */
    public function findAllAvailable(): array
    {
        $statement = $this->connection->query(
            'SELECT id, category_id, name, description, price_in_cents, available
             FROM products
             WHERE available = 1
             ORDER BY name ASC',
        );

        $products = [];

        foreach ($statement->fetchAll() as $row) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function insert(Product $product): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO products (category_id, name, description, price_in_cents, available)
             VALUES (:category_id, :name, :description, :price_in_cents, :available)',
        );

        $statement->execute([
            'category_id' => $product->categoryId(),
            'name' => $product->name(),
            'description' => $product->description(),
            'price_in_cents' => $product->price()->toCents(),
            'available' => $product->isAvailable() ? 1 : 0,
        ]);

        $generatedId = (int) $this->connection->lastInsertId();
        $product->assignId($generatedId);
    }

    private function update(Product $product): void
    {
        $statement = $this->connection->prepare(
            'UPDATE products
             SET name = :name,
                 description = :description,
                 price_in_cents = :price_in_cents,
                 available = :available
             WHERE id = :id',
        );

        $statement->execute([
            'id' => $product->id(),
            'name' => $product->name(),
            'description' => $product->description(),
            'price_in_cents' => $product->price()->toCents(),
            'available' => $product->isAvailable() ? 1 : 0,
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Product
    {
        return Product::reconstruct(
            id: (int) $row['id'],
            name: (string) $row['name'],
            description: (string) $row['description'],
            price: Money::fromCents((int) $row['price_in_cents']),
            categoryId: (int) $row['category_id'],
            available: (bool) $row['available'],
        );
    }
}