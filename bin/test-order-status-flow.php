<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

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

$pdo = ConnectionFactory::fromEnvironment()->connect();
$orderRepository = new PdoOrderRepository($pdo);
$productRepository = new PdoProductRepository($pdo);

$products = $productRepository->findAllAvailable();

if ($products === []) {
    echo "Nenhum produto disponível. Rode antes: php bin/test-product-repository.php\n";
    exit(1);
}

$product = $products[0];

echo "1) Um pedido vazio não pode ser confirmado\n";

try {
    Order::create()->changeStatus(OrderStatus::Confirmed);
    echo "   ERRO: deveria ter sido recusado\n";
} catch (InvalidOrderException $exception) {
    echo '   RECUSADO -> ' . $exception->getMessage() . "\n";
}

$order = Order::create();
$order->addItem(OrderItem::create(
    $product->id(),
    $product->name(),
    $product->price(),
    2,
));
$orderRepository->save($order);

echo sprintf(
    "\n2) Pedido criado e salvo: id %d, status %s, total %s\n",
    $order->id(),
    $order->status()->value,
    $order->total()->format(),
);

echo "\n3) Avançando o status, gravando e relendo do banco\n";

$steps = [
    OrderStatus::Confirmed,
    OrderStatus::Preparing,
    OrderStatus::Ready,
    OrderStatus::Delivered,
];

foreach ($steps as $next) {
    $order->changeStatus($next);
    $orderRepository->save($order);

    $reloaded = $orderRepository->findById($order->id());

    echo sprintf(
        "   gravado: %-9s | lido do banco: %s\n",
        $next->value,
        $reloaded?->status()->value ?? 'NÃO ENCONTRADO',
    );
}

echo "\n4) Tentativas inválidas num pedido já entregue\n";

$attempts = [
    'Entregue -> Pendente' => fn () => $order->changeStatus(OrderStatus::Pending),
    'Adicionar item' => fn () => $order->addItem(OrderItem::create(
        $product->id(),
        $product->name(),
        $product->price(),
        1,
    )),
];

foreach ($attempts as $label => $attempt) {
    try {
        $attempt();
        echo "   {$label}: ERRO, deveria ter sido recusado\n";
    } catch (InvalidOrderException $exception) {
        echo "   {$label}: RECUSADO -> {$exception->getMessage()}\n";
    }
}