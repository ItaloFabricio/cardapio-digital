<?php

declare(strict_types=1);

namespace App\Presentation\Response;

/**
 * Envia um JsonResponse ao cliente.
 */
final class ResponseEmitter
{
    public function emit(JsonResponse $response): void
    {
        http_response_code($response->status()->value);
        header('Content-Type: application/json; charset=utf-8');

        echo $response->toJson();
    }
}