<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Entity\Coupon;
use App\Domain\Specification\CouponNotExpiredSpecification;
use App\Domain\Specification\MinimumOrderValueSpecification;
use App\Domain\ValueObject\Money;

$now = new DateTimeImmutable();

$validCoupon = Coupon::createPercentage(
    'PROMO10',
    10,
    $now->modify('+1 day'),
    Money::fromFloat(30.00),
);

$expiredCoupon = Coupon::createPercentage(
    'VELHO10',
    10,
    $now->modify('-1 day'),
    Money::fromFloat(30.00),
);

$notExpired = new CouponNotExpiredSpecification();
$minimumValue = new MinimumOrderValueSpecification();

$bigOrder = Money::fromFloat(50.00);
$smallOrder = Money::fromFloat(20.00);

$results = [
    'Cupom com validade amanhã: não expirou?' => $notExpired->isSatisfiedBy($validCoupon, $bigOrder, $now),
    'Cupom com validade ontem: não expirou?' => $notExpired->isSatisfiedBy($expiredCoupon, $bigOrder, $now),
    'Pedido de R$ 50,00 (mínimo R$ 30,00): atinge o mínimo?' => $minimumValue->isSatisfiedBy($validCoupon, $bigOrder, $now),
    'Pedido de R$ 20,00 (mínimo R$ 30,00): atinge o mínimo?' => $minimumValue->isSatisfiedBy($validCoupon, $smallOrder, $now),
];

foreach ($results as $description => $result) {
    echo $description . ' ' . ($result ? 'SIM' : 'NÃO') . "\n";
}