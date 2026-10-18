-- Migration 005: cria a tabela de cupons de desconto.

CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    discount_type VARCHAR(20) NOT NULL,
    discount_value INT UNSIGNED NOT NULL,
    valid_until DATETIME NOT NULL,
    minimum_order_value_in_cents INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_coupons_code UNIQUE (code),
    CONSTRAINT chk_coupons_discount_type CHECK (discount_type IN ('percentage', 'fixed')),
    CONSTRAINT chk_coupons_discount_value CHECK (discount_value > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;