<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * Any other client-side API error (4xx) not covered by a dedicated subclass.
 */
final class ApiErrorException extends ApiException
{
}
