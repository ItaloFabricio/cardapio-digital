<?php

declare(strict_types=1);

namespace APP\Domain\ValueObject;

use App\Domain\Exception\InvalidMoneyAmountException;

final class Money
{
    private function __construct(
        private readonly int $amountInCents,
        private readonly string $currency = 'BRL',
    ) {
        if ($amountInCents < 0) {
            throw InvalidMoneyAmountException::negativeAmount($amountInCents);
        }
    }

    public static function fromCents(int $cents, string $currency = 'BRL'): self
    {
        return new self($cents, $currency);
    }

    public static function fromFloat(float $amount, string $currency = 'BRL'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public static function zero(string $currency = 'BRL'): self
    {
        return new self(0, $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountInCents - $other->amountInCents, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amountInCents * $factor), $this->currency);
    }

    public function isGreaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amountInCents > $other->amountInCents;
    }

    public function isGreaterThanOrEqual(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->amountInCents >= $other->amountInCents;
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }

    public function toFloat(): float
    {
        return $this->amountInCents / 100;
    }

    public function toCents(): int
    {
        return $this->amountInCents;
    }

    public function format(): string
    {
        return match ($this->currency) {
            'BRL' => 'R$ ' . number_format($this->toFloat(), 2, ',', '.'),
            default => number_format($this->toFloat(), 2) . ' ' . $this->currency,
        };
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw InvalidMoneyAmountException::currencyMismatch($this->currency, $other->currency);
        }
    }
}