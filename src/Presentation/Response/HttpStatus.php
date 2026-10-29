<?php

declare(strict_types=1);

namespace App\Presentation\Response;

/**
 * Codigos de status HTTP usados pela API.
 */
enum HttpStatus: int
{
    case Ok = 200;
    case Created = 201;
    case BadRequest = 400;
    case NotFound = 404;
    case UnprocessableEntity = 422;
    case InternalServerError = 500;
}