<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * The API could not be reached or failed server-side (network error or 5xx).
 */
final class ApiUnreachableException extends OpenProjectException
{
    public function __construct(
        string $message,
        // The server status for a 5xx; null when the request never completed.
        public readonly ?int $httpStatus = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus ?? 0, $previous);
    }
}
