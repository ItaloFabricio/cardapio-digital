<?php

declare(strict_types=1);

namespace App\Presentation\Response;

use App\Domain\ValueObject\Money;

/**
 * Transforma um Money no formato usado pela API: centavos (exatos, para
 * calculos) e texto formatado (para exibicao).
 */
final class MoneyPresenter
{
    /**
     * @return array{cents: int, formatted: string}
     */
    public function present(Money $money): array
    {
        return [
            'cents' => $money->toCents(),
            'formatted' => $money->format(),
        ];
    }
}
