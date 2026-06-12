<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Authentication;

use GuzzleHttp\Psr7\Request;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiKeyAuthentication::class)]
final class ApiKeyAuthenticationTest extends TestCase
{
    public function testAddsBasicAuthorizationHeaderWithLiteralApikeyUsername(): void
    {
        $authentication = new ApiKeyAuthentication('secret-token');
        $request = new Request('GET', 'https://example.test/api/v3');

        $authenticated = $authentication->authenticate($request);

        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $authenticated->getHeaderLine('Authorization'),
        );
    }

    public function testDoesNotMutateTheOriginalRequest(): void
    {
        $authentication = new ApiKeyAuthentication('secret-token');
        $request = new Request('GET', 'https://example.test/api/v3');

        $authentication->authenticate($request);

        self::assertFalse($request->hasHeader('Authorization'));
    }

    public function testReplacesAPreExistingAuthorizationHeader(): void
    {
        $authentication = new ApiKeyAuthentication('secret-token');
        $request = new Request('GET', 'https://example.test/api/v3', [
            'Authorization' => 'Bearer stale',
        ]);

        $authenticated = $authentication->authenticate($request);

        self::assertSame(
            ['Basic ' . base64_encode('apikey:secret-token')],
            $authenticated->getHeader('Authorization'),
        );
    }
}
