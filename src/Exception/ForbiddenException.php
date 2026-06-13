<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * The authenticated user lacks permission for the resource (HTTP 403).
 */
final class ForbiddenException extends ApiException
{
}
