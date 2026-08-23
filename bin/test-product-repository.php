<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Entity\Product;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoProductRepository;

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

$pdo = ConnectionFactory::fromEnvironment()->connect();

$categoryId = (int) $pdo->query('SELECT id FROM categories LIMIT 1')->fetchColumn();

if ($categoryId === 0) {
    $pdo->exec("INSERT INTO categories (name, display_order) VALUES ('Bebidas', 1)");
    $categoryId = (int) $pdo->lastInsertId();
    echo "Categoria de teste criada (id {$categoryId}).\n";
}

$repository = new PdoProductRepository($pdo);

$product = Product::create(
    name: 'Suco de Laranja 500ml',
    description: 'Suco natural, sem açúcar',
    price: Money::fromFloat(9.90),
    categoryId: $categoryId,
);

echo 'Antes de salvar, id: ' . var_export($product->id(), true) . "\n";

$repository->save($product);

echo "Depois de salvar, id: {$product->id()}\n";

$fetched = $repository->findById($product->id());

echo "Produto recuperado do banco: {$fetched->name()} - {$fetched->price()->format()}\n";

$available = $repository->findAllAvailable();

echo 'Total de produtos disponíveis: ' . count($available) . "\n";