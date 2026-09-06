<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\DTO\CreateOrderInput;
use App\Application\DTO\CreateOrderItemInput;
use App\Application\Service\CreateOrderService;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoOrderRepository;
use App\Infrastructure\Persistence\Pdo\PdoProductRepository;

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

$pdo = ConnectionFactory::fromEnvironment()->connect();

$productId = (int) $pdo->query('SELECT id FROM products LIMIT 1')->fetchColumn();

if ($productId === 0) {
    echo "Nenhum produto encontrado. Rode antes: php bin/test-product-repository.php\n";
    exit(1);
}

$service = new CreateOrderService(
    new PdoProductRepository($pdo),
    new PdoOrderRepository($pdo),
);

$input = new CreateOrderInput(
    customerId: null,
    items: [
        new CreateOrderItemInput(productId: $productId, quantity: 2),
    ],
);

$order = $service->execute($input);

echo "Pedido criado com id: {$order->id()}\n";
echo "Quantidade de itens: {$order->itemCount()}\n";
echo "Total do pedido: {$order->total()->format()}\n";

$fetched = (new PdoOrderRepository($pdo))->findById($order->id());

echo "Pedido recuperado do banco - total: {$fetched->total()->format()}\n";