<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\ListAvailableProductsService;
use App\Domain\Entity\Product;
use App\Presentation\Response\JsonResponse;
use App\Presentation\Response\MoneyPresenter;

/**
 * Controller de produtos: traduz entre HTTP e os casos de uso.
 *
 * Nao contem regra de negocio nem SQL. Chama o Service e transforma o
 * resultado no formato JSON da API.
 */
final class ProductController
{
    public function __construct(
        private readonly ListAvailableProductsService $listAvailableProducts,
        private readonly MoneyPresenter $money = new MoneyPresenter(),
    ) {
    }

    public function listAvailable(): JsonResponse
    {
        $products = $this->listAvailableProducts->execute();

        return JsonResponse::ok([
            'data' => array_map($this->toArray(...), $products),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(Product $product): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'description' => $product->description(),
            'categoryId' => $product->categoryId(),
            'price' => $this->money->present($product->price()),
        ];
    }
}
