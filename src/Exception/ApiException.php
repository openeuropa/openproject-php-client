<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * Base for errors the API reported with an HTTP status and an Error body.
 */
abstract class ApiException extends OpenProjectException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus,
        // The API's machine-readable error URN, when the body carried one.
        public readonly ?string $errorIdentifier = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
