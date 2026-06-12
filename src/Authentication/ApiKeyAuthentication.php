<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 * API key authentication: HTTP Basic with the literal username "apikey".
 *
 * The key is generated in OpenProject under "My account > Access tokens".
 */
final readonly class ApiKeyAuthentication implements AuthenticationInterface
{
    public function __construct(
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
    }

    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader(
            'Authorization',
            'Basic ' . base64_encode('apikey:' . $this->apiKey),
        );
    }
}
