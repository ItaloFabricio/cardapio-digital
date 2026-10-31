<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\ListAvailableProductsService;
use App\Domain\Entity\Product;
use App\Presentation\Response\JsonResponse;

/**
 * Controller de produtos: traduz entre HTTP e os casos de uso.
 */
final class ProductController
{
    public function __construct(
        private readonly ListAvailableProductsService $listAvailableProducts,
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
            'price' => [
                'cents' => $product->price()->toCents(),
                'formatted' => $product->price()->format(),
            ],
        ];
    }
}