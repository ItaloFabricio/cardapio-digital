<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\Service\ApplyCouponService;
use App\Presentation\Request\ApplyCouponRequest;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;
use App\Presentation\Response\MoneyPresenter;
use DateTimeImmutable;

/**
 * Controller de cupons: simula o desconto de um cupom sobre um total.
 *
 * O "agora" nasce aqui, na borda do sistema, e desce como parametro
 * para o Service: nenhuma regra consulta o relogio por conta propria.
 */
final class CouponController
{
    public function __construct(
        private readonly ApplyCouponService $applyCoupon,
        private readonly MoneyPresenter $money = new MoneyPresenter(),
    ) {
    }

    public function simulate(HttpRequest $request): JsonResponse
    {
        $input = ApplyCouponRequest::fromBody($request->body());

        $result = $this->applyCoupon->execute(
            $input->code(),
            $input->orderTotal(),
            new DateTimeImmutable(),
        );

        return JsonResponse::ok([
            'data' => [
                'couponCode' => $result->couponCode,
                'discount' => $this->money->present($result->discount),
                'finalTotal' => $this->money->present($result->finalTotal),
            ],
        ]);
    }
}
