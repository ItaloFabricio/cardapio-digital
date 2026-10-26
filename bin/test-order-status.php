<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Enum\OrderStatus;

echo "Mapa de transições:\n";

foreach (OrderStatus::cases() as $status) {
    $allowed = array_map(
        static fn (OrderStatus $next): string => $next->value,
        $status->allowedTransitions(),
    );

    echo sprintf(
        "%-10s -> %s\n",
        $status->value,
        $status->isFinal() ? '(estado final)' : implode(', ', $allowed),
    );
}

echo "\nVerificações:\n";

$checks = [
    [OrderStatus::Pending, OrderStatus::Confirmed],
    [OrderStatus::Pending, OrderStatus::Delivered],
    [OrderStatus::Preparing, OrderStatus::Cancelled],
    [OrderStatus::Delivered, OrderStatus::Pending],
];

foreach ($checks as [$from, $to]) {
    echo sprintf(
        "%s -> %s: %s\n",
        $from->value,
        $to->value,
        $from->canTransitionTo($to) ? 'SIM' : 'NÃO',
    );
}