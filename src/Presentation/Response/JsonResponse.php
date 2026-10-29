<?php

declare(strict_types=1);

namespace App\Presentation\Response;

/**
 * Descreve uma resposta HTTP em JSON: um status e um conteudo.
 */
final class JsonResponse
{
    /**
     * @param array<mixed> $data
     */
    private function __construct(
        private readonly HttpStatus $status,
        private readonly array $data,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function ok(array $data): self
    {
        return new self(HttpStatus::Ok, $data);
    }

    /**
     * @param array<mixed> $data
     */
    public static function created(array $data): self
    {
        return new self(HttpStatus::Created, $data);
    }

    public static function error(HttpStatus $status, string $message): self
    {
        return new self($status, ['error' => $message]);
    }

    public function status(): HttpStatus
    {
        return $this->status;
    }

    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}