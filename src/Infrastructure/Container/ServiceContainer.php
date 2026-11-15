<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use App\Application\Service\ApplyCouponService;
use App\Application\Service\CreateOrderService;
use App\Application\Service\ListAvailableProductsService;
use App\Application\Strategy\DiscountStrategyFactory;
use App\Domain\Repository\CouponRepositoryInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Specification\CouponNotExpiredSpecification;
use App\Domain\Specification\MinimumOrderValueSpecification;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoCouponRepository;
use App\Infrastructure\Persistence\Pdo\PdoOrderRepository;
use App\Infrastructure\Persistence\Pdo\PdoProductRepository;
use App\Presentation\Controller\CouponController;
use App\Presentation\Controller\OrderController;
use App\Presentation\Controller\ProductController;
use PDO;

/**
 * Monta as pecas do sistema, ligando cada classe as suas dependencias.
 *
 * E o unico lugar autorizado a conhecer TODAS as camadas e as classes
 * concretas. As pecas so sao criadas quando alguem as pede, entao uma
 * rota que nao usa o banco nunca abre conexao com ele.
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

    public function orderRepository(): OrderRepositoryInterface
    {
        return new PdoOrderRepository($this->pdo());
    }

    public function couponRepository(): CouponRepositoryInterface
    {
        return new PdoCouponRepository($this->pdo());
    }

    public function listAvailableProductsService(): ListAvailableProductsService
    {
        return new ListAvailableProductsService($this->productRepository());
    }

    public function createOrderService(): CreateOrderService
    {
        return new CreateOrderService(
            $this->productRepository(),
            $this->orderRepository(),
        );
    }

    public function applyCouponService(): ApplyCouponService
    {
        return new ApplyCouponService(
            $this->couponRepository(),
            new DiscountStrategyFactory(),
            new CouponNotExpiredSpecification(),
            new MinimumOrderValueSpecification(),
        );
    }

    public function productController(): ProductController
    {
        return new ProductController($this->listAvailableProductsService());
    }

    public function orderController(): OrderController
    {
        return new OrderController($this->createOrderService());
    }

    public function couponController(): CouponController
    {
        return new CouponController($this->applyCouponService());
    }
}
