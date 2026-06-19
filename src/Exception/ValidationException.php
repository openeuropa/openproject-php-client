<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

use OpenEuropa\OpenProjectClient\Model\ValidationError;

/**
 * One or more field constraints were violated (HTTP 422).
 */
final class ValidationException extends ApiException
{
    /**
     * @param list<ValidationError> $errors
     *   The per-field validation errors parsed from the response body.
     */
    public function __construct(
        string $message,
        int $httpStatus,
        ?string $errorIdentifier,
        public readonly array $errors,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $errorIdentifier, $previous);
    }
}
