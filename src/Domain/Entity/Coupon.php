<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\DiscountType;
use App\Domain\Exception\InvalidCouponException;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;

/**
 * Coupon é uma Entity: cada cupom cadastrado é unico (tem id) e sera
 * gerenciado individualmente pelo administrador.
 */
final class Coupon
{
    private function __construct(
        private ?int $id,
        private readonly string $code,
        private readonly DiscountType $discountType,
        private readonly int $discountValue,
        private readonly DateTimeImmutable $validUntil,
        private readonly Money $minimumOrderValue,
    ) {
        $this->guardCode($code);
        $this->guardDiscountValue($discountType, $discountValue);
    }

    public static function createPercentage(
        string $code,
        int $percentage,
        DateTimeImmutable $validUntil,
        Money $minimumOrderValue,
    ): self {
        return new self(
            id: null,
            code: self::normalizeCode($code),
            discountType: DiscountType::Percentage,
            discountValue: $percentage,
            validUntil: $validUntil,
            minimumOrderValue: $minimumOrderValue,
        );
    }

    public static function createFixed(
        string $code,
        Money $amount,
        DateTimeImmutable $validUntil,
        Money $minimumOrderValue,
    ): self {
        return new self(
            id: null,
            code: self::normalizeCode($code),
            discountType: DiscountType::Fixed,
            discountValue: $amount->toCents(),
            validUntil: $validUntil,
            minimumOrderValue: $minimumOrderValue,
        );
    }

    public static function reconstruct(
        int $id,
        string $code,
        DiscountType $discountType,
        int $discountValue,
        DateTimeImmutable $validUntil,
        Money $minimumOrderValue,
    ): self {
        return new self(
            $id,
            $code,
            $discountType,
            $discountValue,
            $validUntil,
            $minimumOrderValue,
        );
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw InvalidCouponException::idAlreadyAssigned($this->id);
        }

        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function discountType(): DiscountType
    {
        return $this->discountType;
    }

    public function discountValue(): int
    {
        return $this->discountValue;
    }

    public function validUntil(): DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function minimumOrderValue(): Money
    {
        return $this->minimumOrderValue;
    }

    private static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    private function guardCode(string $code): void
    {
        if (trim($code) === '') {
            throw InvalidCouponException::emptyCode();
        }
    }

    private function guardDiscountValue(DiscountType $type, int $value): void
    {
        $isValid = match ($type) {
            DiscountType::Percentage => $value >= 1 && $value <= 100,
            DiscountType::Fixed => $value > 0,
        };

        if (!$isValid) {
            throw InvalidCouponException::invalidDiscountValue($type->value, $value);
        }
    }
}