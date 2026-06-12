<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Client;
use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use OpenEuropa\OpenProjectClient\Model\InstanceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
#[CoversClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(InstanceInfo::class)]
final class ClientTest extends TestCase
{
    use MockClientTrait;

    public function testInfoDecodesTheRootResponseIntoInstanceInfo(): void
    {
        $client = $this->createClient('http://example.test');
        $this->queueFixtureResponse('root.json');

        $info = $client->info();

        self::assertSame('OpenProject', $info->instanceName);
        self::assertSame('17.5.0', $info->coreVersion);
    }

    public function testInfoSendsAnAuthenticatedGetToTheApiRoot(): void
    {
        $client = $this->createClient('http://example.test');
        $this->queueFixtureResponse('root.json');

        $client->info();

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://example.test/api/v3/', (string) $request->getUri());
        self::assertSame('application/hal+json', $request->getHeaderLine('Accept'));
        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $request->getHeaderLine('Authorization'),
        );
    }

    public function testSupportsSubpathHostedInstances(): void
    {
        $client = $this->createClient('http://example.test/openproject/');
        $this->queueFixtureResponse('root.json');

        $client->info();

        self::assertSame(
            'http://example.test/openproject/api/v3/',
            (string) $this->lastRequest()->getUri(),
        );
    }

    public function testThrowsWhenTheResponseBodyIsNotValidJson(): void
    {
        $client = $this->createClient('http://example.test');
        $this->queueJsonResponse('<html>not json</html>');

        $this->expectException(OpenProjectException::class);
        $this->expectExceptionMessage('not valid JSON');

        $client->info();
    }

    private function createClient(string $baseUrl): Client
    {
        $httpFactory = new HttpFactory();

        return new Client(
            $baseUrl,
            new ApiKeyAuthentication('secret-token'),
            $this->createMockClient(),
            $httpFactory,
            $httpFactory,
        );
    }
}
