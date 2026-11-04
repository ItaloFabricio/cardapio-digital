<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Service\CreateOrderService;
use App\Domain\Entity\Order;
use App\Domain\Entity\Product;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Http\Router;
use App\Presentation\Controller\OrderController;
use App\Presentation\HttpKernel;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;

// Tres produtos: dois disponiveis e um indisponivel.
$products = [
    1 => Product::reconstruct(1, 'Suco de Laranja 500ml', 'Suco natural', Money::fromFloat(9.90), 1, true),
    2 => Product::reconstruct(2, 'Água mineral 500ml', 'Sem gás', Money::fromCents(500), 1, true),
    3 => Product::reconstruct(3, 'Bolo de Chocolate', 'Fatia', Money::fromFloat(12.00), 1, true),
];
$products[3]->markAsUnavailable();

// Repositorios FALSOS, em memoria: nenhum banco e necessario.
$productRepository = new class ($products) implements ProductRepositoryInterface {
    /**
     * @param array<int, Product> $products
     */
    public function __construct(private readonly array $products)
    {
    }

    public function save(Product $product): void
    {
    }

    public function findById(int $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

    public function findAllAvailable(): array
    {
        return array_values($this->products);
    }

    public function delete(int $id): void
    {
    }
};

$orderRepository = new class implements OrderRepositoryInterface {
    private int $lastId = 0;

    public function save(Order $order): void
    {
        if ($order->id() === null) {
            $order->assignId(++$this->lastId);
        }
    }

    public function findById(int $id): ?Order
    {
        return null;
    }
};

$controller = new OrderController(new CreateOrderService($productRepository, $orderRepository));

$router = new Router();
$router->post(
    '/orders',
    static fn (HttpRequest $request): JsonResponse => $controller->create($request),
);

$kernel = new HttpKernel($router);

$scenarios = [
    'Pedido válido com dois produtos' => '{"items":[{"productId":1,"quantity":2},{"productId":2,"quantity":1}]}',
    'Produto que não existe' => '{"items":[{"productId":999,"quantity":1}]}',
    'Produto indisponível' => '{"items":[{"productId":3,"quantity":1}]}',
    'Lista de itens vazia' => '{"items":[]}',
    'Tipos errados (productId em texto, quantidade zero)' => '{"items":[{"productId":"1","quantity":0}]}',
    'JSON quebrado' => '{"items": [',
    'Sem corpo nenhum' => '',
];

$number = 1;

foreach ($scenarios as $label => $body) {
    $response = $kernel->handle('POST', '/orders', $body);

    echo "{$number}) {$label}\n";
    echo "   status: {$response->status()->value} {$response->status()->name}\n";
    echo "   corpo: {$response->toJson()}\n\n";

    $number++;
}