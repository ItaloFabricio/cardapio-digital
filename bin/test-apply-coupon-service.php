<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Service\ApplyCouponService;
use App\Application\Strategy\DiscountStrategyFactory;
use App\Domain\Entity\Coupon;
use App\Domain\Exception\InvalidCouponException;
use App\Domain\Specification\CouponNotExpiredSpecification;
use App\Domain\Specification\MinimumOrderValueSpecification;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoCouponRepository;

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Montagem manual de todas as pecas (o "wiring"): e aqui, e so aqui,
// que as classes concretas sao escolhidas e ligadas ao Service.
$pdo = ConnectionFactory::fromEnvironment()->connect();
$repository = new PdoCouponRepository($pdo);

$service = new ApplyCouponService(
    $repository,
    new DiscountStrategyFactory(),
    new CouponNotExpiredSpecification(),
    new MinimumOrderValueSpecification(),
);

$now = new DateTimeImmutable();
$tomorrow = $now->modify('+1 day');
$yesterday = $now->modify('-1 day');

// Codigos diferentes a cada execucao, porque a coluna code e UNIQUE.
$suffix = date('His');

$repository->save(Coupon::createPercentage("OK{$suffix}", 10, $tomorrow, Money::fromFloat(30.00)));
$repository->save(Coupon::createPercentage("VELHO{$suffix}", 10, $yesterday, Money::zero()));
$repository->save(Coupon::createFixed("CARO{$suffix}", Money::fromFloat(5.00), $tomorrow, Money::fromFloat(100.00)));

$scenarios = [
    ['Cupom válido (digitado em minúsculas), pedido de R$ 50,00', "ok{$suffix}", Money::fromFloat(50.00)],
    ['Cupom válido, pedido de R$ 20,00 (mínimo é R$ 30,00)', "OK{$suffix}", Money::fromFloat(20.00)],
    ['Cupom expirado ontem, pedido de R$ 50,00', "VELHO{$suffix}", Money::fromFloat(50.00)],
    ['Cupom com mínimo de R$ 100,00, pedido de R$ 50,00', "CARO{$suffix}", Money::fromFloat(50.00)],
    ['Cupom que não existe', 'NAOEXISTE', Money::fromFloat(50.00)],
];

foreach ($scenarios as [$label, $code, $total]) {
    echo $label . "\n";

    try {
        $result = $service->execute($code, $total, $now);

        echo sprintf(
            "   APLICADO  -> desconto: %s | total final: %s\n\n",
            $result->discount->format(),
            $result->finalTotal->format(),
        );
    } catch (InvalidCouponException $exception) {
        echo '   RECUSADO  -> ' . $exception->getMessage() . "\n\n";
    }
}