<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Service\ChangeOrderStatusService;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\InvalidOrderException;
use App\Domain\ValueObject\OrderItem;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoOrderRepository;
use App\Infrastructure\Persistence\Pdo\PdoProductRepository;

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Montagem manual (Composition Root): aqui as classes concretas sao
// escolhidas e ligadas ao Service.
$pdo = ConnectionFactory::fromEnvironment()->connect();
$orderRepository = new PdoOrderRepository($pdo);
$productRepository = new PdoProductRepository($pdo);
$service = new ChangeOrderStatusService($orderRepository);

$products = $productRepository->findAllAvailable();

if ($products === []) {
    echo "Nenhum produto disponível. Rode antes: php bin/test-product-repository.php\n";
    exit(1);
}

$product = $products[0];

$order = Order::create();
$order->addItem(OrderItem::create(
    $product->id(),
    $product->name(),
    $product->price(),
    2,
));
$orderRepository->save($order);

echo sprintf(
    "Pedido de teste criado: id %d (status %s)\n\n",
    $order->id(),
    $order->status()->value,
);

$scenarios = [
    ['Pendente -> Confirmado', $order->id(), OrderStatus::Confirmed],
    ['Confirmado -> Pronto (pulando o preparo)', $order->id(), OrderStatus::Ready],
    ['Confirmado -> Em preparo', $order->id(), OrderStatus::Preparing],
    ['Em preparo -> Cancelado', $order->id(), OrderStatus::Cancelled],
    ['Em preparo -> Pronto', $order->id(), OrderStatus::Ready],
    ['Pronto -> Entregue', $order->id(), OrderStatus::Delivered],
    ['Pedido que não existe', 999999, OrderStatus::Confirmed],
];

foreach ($scenarios as [$label, $orderId, $newStatus]) {
    echo $label . "\n";

    try {
        $updated = $service->execute($orderId, $newStatus);

        echo "   APLICADO  -> status agora: {$updated->status()->value}\n";
    } catch (InvalidOrderException $exception) {
        echo '   RECUSADO  -> ' . $exception->getMessage() . "\n";
    }
}

$final = $orderRepository->findById($order->id());

echo "\nStatus final lido do banco: " . ($final?->status()->value ?? 'NÃO ENCONTRADO') . "\n";