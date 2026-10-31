<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use App\Application\Service\ListAvailableProductsService;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoProductRepository;
use App\Presentation\Controller\ProductController;
use PDO;

/**
 * Monta as pecas do sistema, ligando cada classe as suas dependencias.
 */
final class ServiceContainer
{
    private ?PDO $pdo = null;

    public function pdo(): PDO
    {
        return $this->pdo ??= ConnectionFactory::fromEnvironment()->connect();
    }

    public function productRepository(): ProductRepositoryInterface
    {
        return new PdoProductRepository($this->pdo());
    }

    public function listAvailableProductsService(): ListAvailableProductsService
    {
        return new ListAvailableProductsService($this->productRepository());
    }

    public function productController(): ProductController
    {
        return new ProductController($this->listAvailableProductsService());
    }
}