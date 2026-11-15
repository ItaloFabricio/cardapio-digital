<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Domain\ValueObject\Money;

/**
 * Valida o FORMATO do corpo de POST /coupons/simulate.
 *
 * Regras de negocio (o cupom existe? esta valido?) pertencem ao
 * ApplyCouponService. O total informado so serve para SIMULAR o
 * desconto: nada e gravado a partir dele.
 */
final class ApplyCouponRequest
{
    private const MAX_CODE_LENGTH = 50;

    private function __construct(
        private readonly string $code,
        private readonly Money $orderTotal,
    ) {
    }

    /**
     * @param array<mixed> $body
     *
     * @throws ValidationException
     */
    public static function fromBody(array $body): self
    {
        $errors = [];

        $code = $body['code'] ?? null;

        if (!is_string($code) || trim($code) === '' || strlen(trim($code)) > self::MAX_CODE_LENGTH) {
            $errors['code'] = sprintf(
                'deve ser um texto não vazio de até %d caracteres.',
                self::MAX_CODE_LENGTH,
            );
        }

        $total = $body['orderTotalCents'] ?? null;

        if (!is_int($total) || $total <= 0) {
            $errors['orderTotalCents'] = 'deve ser um número inteiro positivo (em centavos).';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self(trim($code), Money::fromCents($total));
    }

    public function code(): string
    {
        return $this->code;
    }

    public function orderTotal(): Money
    {
        return $this->orderTotal;
    }
}
