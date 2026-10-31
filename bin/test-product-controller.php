<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Service\ListAvailableProductsService;
use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Presentation\Controller\ProductController;

/**
 * Monta um Controller usando um repositorio FALSO, que devolve apenas os
 * produtos recebidos. Nenhum banco de dados e necessario: o Service e o
 * Controller nao sabem (nem precisam saber) que o repositorio e falso.
 *
 * @param array<Product> $products
 */
$makeController = static fn (array $products): ProductController => new ProductController(
    new ListAvailableProductsService(
        new class ($products) implements ProductRepositoryInterface {
            /**
             * @param array<Product> $products
             */
            public function __construct(private readonly array $products)
            {
            }

            public function save(Product $product): void
            {
            }

            public function findById(int $id): ?Product
            {
                return null;
            }

            public function findAllAvailable(): array
            {
                return $this->products;
            }

            public function delete(int $id): void
            {
            }
        },
    ),
);

$products = [
    Product::reconstruct(1, 'Suco de Laranja 500ml', 'Suco natural, sem açúcar', Money::fromFloat(9.90), 1, true),
    Product::reconstruct(2, 'Água mineral 500ml', 'Sem gás', Money::fromCents(500), 1, true),
];

$scenarios = [
    'Controller com dois produtos' => $products,
    'Controller sem nenhum produto' => [],
];

$number = 1;

foreach ($scenarios as $label => $scenarioProducts) {
    $response = $makeController($scenarioProducts)->listAvailable();

    echo "{$number}) {$label}\n";
    echo "   status: {$response->status()->value} {$response->status()->name}\n";
    echo "   corpo: {$response->toJson()}\n\n";

    $number++;
}