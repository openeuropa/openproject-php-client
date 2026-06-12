<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 * Applies authentication credentials to outgoing API requests.
 */
interface AuthenticationInterface
{
    /**
     * Returns a copy of the request with authentication credentials applied.
     */
    public function authenticate(RequestInterface $request): RequestInterface;
}
