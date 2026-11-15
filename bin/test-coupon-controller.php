<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Service\ApplyCouponService;
use App\Application\Strategy\DiscountStrategyFactory;
use App\Domain\Entity\Coupon;
use App\Domain\Enum\DiscountType;
use App\Domain\Repository\CouponRepositoryInterface;
use App\Domain\Specification\CouponNotExpiredSpecification;
use App\Domain\Specification\MinimumOrderValueSpecification;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Http\Router;
use App\Presentation\Controller\CouponController;
use App\Presentation\ExceptionHandler;
use App\Presentation\HttpKernel;
use App\Presentation\Request\HttpRequest;
use App\Presentation\Response\JsonResponse;

$tomorrow = (new DateTimeImmutable())->modify('+1 day');
$yesterday = (new DateTimeImmutable())->modify('-1 day');

// Tres cupons: um percentual com minimo, um expirado e um fixo maior que qualquer pedido pequeno.
$coupons = [
    'PROMO10' => Coupon::reconstruct(1, 'PROMO10', DiscountType::Percentage, 10, $tomorrow, Money::fromCents(3000)),
    'VELHO' => Coupon::reconstruct(2, 'VELHO', DiscountType::Percentage, 10, $yesterday, Money::zero()),
    'DESC100' => Coupon::reconstruct(3, 'DESC100', DiscountType::Fixed, 10000, $tomorrow, Money::zero()),
];

// Repositorio FALSO, em memoria: nenhum banco e necessario.
$couponRepository = new class ($coupons) implements CouponRepositoryInterface {
    /**
     * @param array<string, Coupon> $coupons
     */
    public function __construct(private readonly array $coupons)
    {
    }

    public function save(Coupon $coupon): void
    {
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->coupons[Coupon::normalizeCode($code)] ?? null;
    }
};

$controller = new CouponController(new ApplyCouponService(
    $couponRepository,
    new DiscountStrategyFactory(),
    new CouponNotExpiredSpecification(),
    new MinimumOrderValueSpecification(),
));

$router = new Router();
$router->post(
    '/coupons/simulate',
    static fn (HttpRequest $request): JsonResponse => $controller->simulate($request),
);

$kernel = new HttpKernel($router, new ExceptionHandler());

$scenarios = [
    'Cupom válido (digitado em minúsculas), pedido de R$ 50,00' => '{"code":"promo10","orderTotalCents":5000}',
    'Pedido de R$ 20,00 (abaixo do mínimo de R$ 30,00)' => '{"code":"PROMO10","orderTotalCents":2000}',
    'Cupom expirado' => '{"code":"VELHO","orderTotalCents":5000}',
    'Cupom que não existe' => '{"code":"NAOEXISTE","orderTotalCents":5000}',
    'Cupom fixo de R$ 100,00 num pedido de R$ 80,00' => '{"code":"DESC100","orderTotalCents":8000}',
    'Corpo sem nenhum campo' => '{}',
    'Tipos errados (código número, total em texto)' => '{"code":123,"orderTotalCents":"5000"}',
];

$number = 1;

foreach ($scenarios as $label => $body) {
    $response = $kernel->handle('POST', '/coupons/simulate', $body);

    echo "{$number}) {$label}\n";
    echo "   status: {$response->status()->value} {$response->status()->name}\n";
    echo "   corpo: {$response->toJson()}\n\n";

    $number++;
}
