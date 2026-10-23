<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Strategy\DiscountStrategyFactory;
use App\Domain\Entity\Coupon;
use App\Domain\ValueObject\Money;

$validUntil = (new DateTimeImmutable())->modify('+1 day');
$noMinimum = Money::zero();

$coupons = [
    Coupon::createPercentage('PROMO10', 10, $validUntil, $noMinimum),
    Coupon::createFixed('DESC5', Money::fromFloat(5.00), $validUntil, $noMinimum),
    Coupon::createFixed('DESC100', Money::fromFloat(100.00), $validUntil, $noMinimum),
];

$orderTotal = Money::fromFloat(80.00);
$factory = new DiscountStrategyFactory();

echo 'Total do pedido: ' . $orderTotal->format() . "\n\n";

foreach ($coupons as $coupon) {
    $strategy = $factory->createFor($coupon);
    $discount = $strategy->apply($orderTotal);
    $finalTotal = $orderTotal->subtract($discount);

    echo sprintf(
        "%s -> estratégia: %s | desconto: %s | total final: %s\n",
        $coupon->code(),
        (new ReflectionClass($strategy))->getShortName(),
        $discount->format(),
        $finalTotal->format(),
    );
}