<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Entity\Coupon;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Persistence\Connection\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoCouponRepository;

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        $_ENV[$key] = $value;
    }
}

$pdo = ConnectionFactory::fromEnvironment()->connect();
$repository = new PdoCouponRepository($pdo);

// Codigo diferente a cada execucao, porque a coluna code e UNIQUE.
$code = 'TESTE' . date('His');

$coupon = Coupon::createFixed(
    $code,
    Money::fromFloat(5.00),
    (new DateTimeImmutable())->modify('+7 days'),
    Money::fromFloat(30.00),
);

echo 'Antes de salvar, id: ' . var_export($coupon->id(), true) . "\n";

$repository->save($coupon);

echo "Depois de salvar, id: {$coupon->id()}\n";

$searched = strtolower($code);
$found = $repository->findByCode($searched);

if ($found === null) {
    echo "ERRO: o cupom salvo não foi encontrado.\n";
    exit(1);
}

echo "Busca por '{$searched}' encontrou o código: {$found->code()}\n";
echo "Tipo: {$found->discountType()->value} | valor: {$found->discountValue()} centavos\n";
echo 'Válido até: ' . $found->validUntil()->format('d/m/Y H:i') . "\n";
echo 'Pedido mínimo: ' . $found->minimumOrderValue()->format() . "\n";

$missing = $repository->findByCode('NAOEXISTE');

echo 'Busca por cupom inexistente: ' . ($missing === null ? 'null (como esperado)' : 'ERRO') . "\n";