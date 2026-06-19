<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Exception;

/**
 * A write conflicted with a concurrent modification (HTTP 409).
 */
final class ConflictException extends ApiException
{
}
