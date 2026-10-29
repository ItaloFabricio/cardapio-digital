<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use JsonException;

/**
 * Representa uma requisicao HTTP: metodo, caminho e corpo JSON ja decodificado.
 */
final class HttpRequest
{
    /**
     * @param array<mixed> $body
     */
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $body,
    ) {
    }

    public static function fromGlobals(): self
    {
        return self::fromRaw(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            (string) file_get_contents('php://input'),
        );
    }

    public static function fromRaw(string $method, string $uri, string $rawBody): self
    {
        return new self(
            strtoupper($method),
            parse_url($uri, PHP_URL_PATH) ?: '/',
            self::decodeBody($rawBody),
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<mixed>
     */
    public function body(): array
    {
        return $this->body;
    }

    /**
     * @return array<mixed>
     */
    private static function decodeBody(string $rawBody): array
    {
        if (trim($rawBody) === '') {
            return [];
        }

        try {
            $decoded = json_decode($rawBody, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw InvalidRequestBodyException::malformedJson();
        }

        if (!is_array($decoded) || ($decoded !== [] && array_is_list($decoded))) {
            throw InvalidRequestBodyException::notAnObject();
        }

        return $decoded;
    }
}