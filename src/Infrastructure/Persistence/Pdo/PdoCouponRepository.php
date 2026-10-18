<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Entity\Coupon;
use App\Domain\Enum\DiscountType;
use App\Domain\Exception\InvalidCouponException;
use App\Domain\Repository\CouponRepositoryInterface;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;
use PDO;

final class PdoCouponRepository implements CouponRepositoryInterface
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function save(Coupon $coupon): void
    {
        if ($coupon->id() !== null) {
            throw InvalidCouponException::idAlreadyAssigned($coupon->id());
        }

        $statement = $this->connection->prepare(
            'INSERT INTO coupons
                (code, discount_type, discount_value, valid_until, minimum_order_value_in_cents)
             VALUES
                (:code, :discount_type, :discount_value, :valid_until, :minimum_order_value_in_cents)',
        );

        $statement->execute([
            'code' => $coupon->code(),
            'discount_type' => $coupon->discountType()->value,
            'discount_value' => $coupon->discountValue(),
            'valid_until' => $coupon->validUntil()->format(self::DATE_FORMAT),
            'minimum_order_value_in_cents' => $coupon->minimumOrderValue()->toCents(),
        ]);

        $coupon->assignId((int) $this->connection->lastInsertId());
    }

    public function findByCode(string $code): ?Coupon
    {
        $statement = $this->connection->prepare(
            'SELECT id, code, discount_type, discount_value, valid_until, minimum_order_value_in_cents
             FROM coupons
             WHERE code = :code',
        );

        $statement->execute(['code' => Coupon::normalizeCode($code)]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Coupon
    {
        return Coupon::reconstruct(
            id: (int) $row['id'],
            code: (string) $row['code'],
            discountType: DiscountType::from((string) $row['discount_type']),
            discountValue: (int) $row['discount_value'],
            validUntil: new DateTimeImmutable((string) $row['valid_until']),
            minimumOrderValue: Money::fromCents((int) $row['minimum_order_value_in_cents']),
        );
    }
}