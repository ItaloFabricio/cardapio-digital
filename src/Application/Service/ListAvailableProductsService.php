<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;

/**
 * Caso de uso: "listar os produtos disponiveis para pedido".
 */
final class ListAvailableProductsService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * @return list<Product>
     */
    public function execute(): array
    {
        return $this->productRepository->findAllAvailable();
    }
}