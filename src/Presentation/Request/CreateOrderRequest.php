<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Application\DTO\CreateOrderInput;
use App\Application\DTO\CreateOrderItemInput;

/**
 * Valida o FORMATO do corpo de POST /orders e o transforma no DTO que o
 * caso de uso espera.
 */
final class CreateOrderRequest
{
    private function __construct(
        private readonly CreateOrderInput $input,
    ) {
    }

    /**
     * @param array<mixed> $body
     *
     * @throws ValidationException
     */
    public static function fromBody(array $body): self
    {
        $rawItems = $body['items'] ?? null;

        if (!is_array($rawItems) || $rawItems === [] || !array_is_list($rawItems)) {
            throw new ValidationException([
                'items' => 'deve ser uma lista com ao menos um item.',
            ]);
        }

        $items = [];
        $errors = [];

        foreach ($rawItems as $index => $rawItem) {
            $itemErrors = self::validateItem($rawItem);

            foreach ($itemErrors as $field => $message) {
                $errors["items.{$index}.{$field}"] = $message;
            }

            if ($itemErrors === []) {
                $items[] = new CreateOrderItemInput(
                    (int) $rawItem['productId'],
                    (int) $rawItem['quantity'],
                );
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self(new CreateOrderInput(null, $items));
    }

    public function toInput(): CreateOrderInput
    {
        return $this->input;
    }

    /**
     * @return array<string, string>
     */
    private static function validateItem(mixed $rawItem): array
    {
        $item = is_array($rawItem) ? $rawItem : [];
        $errors = [];

        foreach (['productId', 'quantity'] as $field) {
            $value = $item[$field] ?? null;

            if (!is_int($value) || $value <= 0) {
                $errors[$field] = 'deve ser um número inteiro positivo.';
            }
        }

        return $errors;
    }
}