<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Exception;

use OpenEuropa\OpenProjectClient\Exception\ApiException;
use OpenEuropa\OpenProjectClient\Exception\ApiUnreachableException;
use OpenEuropa\OpenProjectClient\Exception\AuthenticationException;
use OpenEuropa\OpenProjectClient\Exception\ForbiddenException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiException::class)]
#[CoversClass(ApiUnreachableException::class)]
#[UsesClass(AuthenticationException::class)]
#[UsesClass(ForbiddenException::class)]
final class ApiExceptionTest extends TestCase
{
    public function testCarriesTheHttpStatusIdentifierAndPrevious(): void
    {
        $previous = new \RuntimeException('boom');
        $exception = new AuthenticationException(
            'Credentials were rejected.',
            401,
            'urn:openproject-org:api:v3:errors:Unauthenticated',
            $previous,
        );

        self::assertSame('Credentials were rejected.', $exception->getMessage());
        self::assertSame(401, $exception->httpStatus);
        self::assertSame(401, $exception->getCode());
        self::assertSame(
            'urn:openproject-org:api:v3:errors:Unauthenticated',
            $exception->errorIdentifier,
        );
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testIdentifierAndPreviousDefaultToNull(): void
    {
        $exception = new ForbiddenException('Permission denied.', 403);

        self::assertSame(403, $exception->httpStatus);
        self::assertNull($exception->errorIdentifier);
        self::assertNull($exception->getPrevious());
    }

    public function testApiUnreachableCarriesAServerStatus(): void
    {
        $exception = new ApiUnreachableException('The server failed.', 503);

        self::assertSame(503, $exception->httpStatus);
        self::assertSame(503, $exception->getCode());
    }

    public function testApiUnreachableDefaultsToNoStatusForNetworkFailures(): void
    {
        $previous = new \RuntimeException('Connection refused');
        $exception = new ApiUnreachableException('Could not reach the API.', previous: $previous);

        self::assertNull($exception->httpStatus);
        self::assertSame(0, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
