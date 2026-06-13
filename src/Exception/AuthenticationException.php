<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * The credentials were missing or rejected (HTTP 401).
 */
final class AuthenticationException extends ApiException
{
}
