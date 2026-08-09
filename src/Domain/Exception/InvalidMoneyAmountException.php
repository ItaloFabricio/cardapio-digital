<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class InvalidMoneyAmountException extends DomainException
{

    public static function negativeAmount(int $cents): self
    {
        return new self(sprintf(
            'Valor monetário não pode ser negativo. Recebido: %d centavos.',
            $cents,
        ));
    }

    public static function currencyMismatch(string $expected, string $given): self
    {
        return new self(sprintf(
            'Operação inválida entre moedas diferentes: "%s" e "%s".',
            $expected,
            $given
        ));
    }

}