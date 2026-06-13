<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Http;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Exception\ApiErrorException;
use OpenEuropa\OpenProjectClient\Exception\ApiException;
use OpenEuropa\OpenProjectClient\Exception\ApiUnreachableException;
use OpenEuropa\OpenProjectClient\Exception\AuthenticationException;
use OpenEuropa\OpenProjectClient\Exception\ForbiddenException;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

#[CoversClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(ApiException::class)]
#[UsesClass(AuthenticationException::class)]
#[UsesClass(ForbiddenException::class)]
#[UsesClass(NotFoundException::class)]
#[UsesClass(ApiErrorException::class)]
#[UsesClass(ApiUnreachableException::class)]
final class TransportTest extends TestCase
{
    use MockClientTrait;

    public function testSendsAnAuthenticatedHalGetToTheApiRoot(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}');

        $transport->get();

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://example.test/api/v3/', (string) $request->getUri());
        self::assertSame('application/hal+json', $request->getHeaderLine('Accept'));
        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $request->getHeaderLine('Authorization'),
        );
    }

    public function testResolvesResourcePathsAgainstTheApiRoot(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}');

        $transport->get('work_packages/42');

        self::assertSame(
            'http://example.test/api/v3/work_packages/42',
            (string) $this->lastRequest()->getUri(),
        );
    }

    public function testSupportsSubpathHostedInstances(): void
    {
        $transport = $this->createTransport('http://example.test/openproject/');
        $this->queueJsonResponse('{}');

        $transport->get();

        self::assertSame(
            'http://example.test/openproject/api/v3/',
            (string) $this->lastRequest()->getUri(),
        );
    }

    public function testDecodesTheJsonBody(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('root.json');

        self::assertSame('OpenProject', $transport->get()['instanceName']);
    }

    public function testThrowsWhenTheResponseBodyIsNotValidJson(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('<html>not json</html>');

        $this->expectException(OpenProjectException::class);
        $this->expectExceptionMessage('not valid JSON');

        $transport->get();
    }

    public function testMapsUnauthenticatedResponseToAuthenticationException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-401-unauthenticated.json', 401);

        try {
            $transport->get();
            self::fail('Expected an AuthenticationException.');
        } catch (AuthenticationException $exception) {
            self::assertSame(401, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:Unauthenticated',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsForbiddenResponseToForbiddenException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-403-missing-permission.json', 403);

        try {
            $transport->get();
            self::fail('Expected a ForbiddenException.');
        } catch (ForbiddenException $exception) {
            self::assertSame(403, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:MissingPermission',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsNotFoundResponseToNotFoundException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-404-not-found.json', 404);

        try {
            $transport->get();
            self::fail('Expected a NotFoundException.');
        } catch (NotFoundException $exception) {
            self::assertSame(404, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:NotFound',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsOtherClientErrorToApiErrorException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-400-invalid-query.json', 400);

        try {
            $transport->get();
            self::fail('Expected an ApiErrorException.');
        } catch (ApiErrorException $exception) {
            self::assertSame(400, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:InvalidQuery',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsServerErrorToApiUnreachableException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-500-internal.json', 500);

        try {
            $transport->get();
            self::fail('Expected an ApiUnreachableException.');
        } catch (ApiUnreachableException $exception) {
            self::assertSame(500, $exception->httpStatus);
        }
    }

    public function testMapsNetworkFailureToApiUnreachableException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueConnectionFailure();

        try {
            $transport->get();
            self::fail('Expected an ApiUnreachableException.');
        } catch (ApiUnreachableException $exception) {
            self::assertNull($exception->httpStatus);
            self::assertInstanceOf(ClientExceptionInterface::class, $exception->getPrevious());
        }
    }

    private function createTransport(string $baseUrl): Transport
    {
        $httpFactory = new HttpFactory();

        return new Transport(
            $baseUrl,
            new ApiKeyAuthentication('secret-token'),
            $this->createMockClient(),
            $httpFactory,
            $httpFactory,
        );
    }
}
