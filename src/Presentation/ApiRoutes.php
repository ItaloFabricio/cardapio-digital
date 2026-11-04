<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Infrastructure\Container\ServiceContainer;
use App\Infrastructure\Http\RouterInterface;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;

/**
 * Lista de todas as rotas da API.
 */
final class ApiRoutes
{
    public function __construct(
        private readonly ServiceContainer $container,
    ) {
    }

    public function register(RouterInterface $router): void
    {
        $router->get('/', static fn (): JsonResponse => JsonResponse::ok([
            'name' => 'Cardápio Digital Inteligente',
            'status' => 'online',
        ]));

        $router->get('/health', static fn (): JsonResponse => JsonResponse::ok([
            'status' => 'ok',
        ]));

        $router->get(
            '/products',
            fn (): JsonResponse => $this->container->productController()->listAvailable(),
        );

        $router->post(
            '/orders',
            fn (HttpRequest $request): JsonResponse => $this->container->orderController()->create($request),
        );
    }
}