<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\CreateOrderService;
use App\Domain\Entity\Order;
use App\Domain\ValueObject\OrderItem;
use App\Presentation\Request\CreateOrderRequest;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;
use App\Presentation\Response\MoneyPresenter;

/**
 * Controller de pedidos: traduz entre HTTP e os casos de uso.
 *
 * Nao captura excecoes de proposito: erros de validacao e de regra de
 * negocio sobem ate o HttpKernel, que os entrega ao ExceptionHandler.
 */
final class OrderController
{
    public function __construct(
        private readonly CreateOrderService $createOrder,
        private readonly MoneyPresenter $money = new MoneyPresenter(),
    ) {
    }

    public function create(HttpRequest $request): JsonResponse
    {
        $input = CreateOrderRequest::fromBody($request->body())->toInput();

        $order = $this->createOrder->execute($input);

        return JsonResponse::created([
            'data' => $this->orderToArray($order),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderToArray(Order $order): array
    {
        return [
            'id' => $order->id(),
            'status' => $order->status()->value,
            'statusLabel' => $order->status()->label(),
            'items' => array_map($this->itemToArray(...), $order->items()),
            'total' => $this->money->present($order->total()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemToArray(OrderItem $item): array
    {
        return [
            'productId' => $item->productId(),
            'productName' => $item->productName(),
            'quantity' => $item->quantity(),
            'unitPrice' => $this->money->present($item->unitPrice()),
            'subtotal' => $this->money->present($item->subtotal()),
        ];
    }
}
