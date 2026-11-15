<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Entity\Coupon;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Config\EnvironmentLoader;
use App\Infrastructure\Config\TimezoneConfigurator;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoCouponRepository;

// Mesmo ambiente e mesmo fuso horario do servidor (public/index.php).
(new EnvironmentLoader())->load(dirname(__DIR__) . '/.env');
(new TimezoneConfigurator())->configure($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

$repository = new PdoCouponRepository(ConnectionFactory::fromEnvironment()->connect());

if ($repository->findByCode('PROMO10') !== null) {
    echo "O cupom PROMO10 já existe. Nada a fazer.\n";
    exit(0);
}

$coupon = Coupon::createPercentage(
    'PROMO10',
    10,
    (new DateTimeImmutable())->modify('+30 days'),
    Money::fromFloat(30.00),
);

$repository->save($coupon);

echo sprintf(
    "Cupom %s criado (id %d): %d%% de desconto, pedido mínimo %s, válido até %s.\n",
    $coupon->code(),
    $coupon->id(),
    $coupon->discountValue(),
    $coupon->minimumOrderValue()->format(),
    $coupon->validUntil()->format('d/m/Y H:i'),
);
