<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\CreateOrderService;
use App\Domain\Entity\Order;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\OrderItem;
use App\Presentation\Request\CreateOrderRequest;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;

/**
 * Controller de pedidos: traduz entre HTTP e os casos de uso.
 */
final class OrderController
{
    public function __construct(
        private readonly CreateOrderService $createOrder,
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
            'items' => array_map($this->itemToArray(...), $order->items()),
            'total' => $this->moneyToArray($order->total()),
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
            'unitPrice' => $this->moneyToArray($item->unitPrice()),
            'subtotal' => $this->moneyToArray($item->subtotal()),
        ];
    }

    /**
     * @return array{cents: int, formatted: string}
     */
    private function moneyToArray(Money $money): array
    {
        return [
            'cents' => $money->toCents(),
            'formatted' => $money->format(),
        ];
    }
}