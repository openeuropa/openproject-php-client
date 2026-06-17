<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Http\Transport;

/**
 * Base class for all endpoint classes, providing transport access.
 */
abstract class AbstractEndpoint
{
    public function __construct(protected readonly Transport $transport)
    {
    }
}
