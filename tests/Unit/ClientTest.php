<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Client;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ConfigurationEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\PrioritiesEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ProjectsEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\StatusesEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\TypesEndpoint;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\InstanceInfo;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
#[UsesClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(InstanceInfo::class)]
#[UsesClass(ConfigurationEndpoint::class)]
#[UsesClass(StatusesEndpoint::class)]
#[UsesClass(TypesEndpoint::class)]
#[UsesClass(PrioritiesEndpoint::class)]
#[UsesClass(ProjectsEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
final class ClientTest extends TestCase
{
    use MockClientTrait;

    public function testInfoReturnsInstanceInfoFromTheRootResponse(): void
    {
        $client = $this->createClient('http://example.test');
        $this->queueFixtureResponse('root.json');

        $info = $client->info();

        self::assertSame('OpenProject', $info->instanceName);
        self::assertSame('17.5.0', $info->coreVersion);
    }

    public function testConfigurationReturnsAConfigurationEndpoint(): void
    {
        $client = $this->createClient('http://example.test');

        self::assertInstanceOf(ConfigurationEndpoint::class, $client->configuration());
    }

    public function testStatusesReturnsAStatusesEndpoint(): void
    {
        $client = $this->createClient('http://example.test');

        self::assertInstanceOf(StatusesEndpoint::class, $client->statuses());
    }

    public function testTypesReturnsATypesEndpoint(): void
    {
        $client = $this->createClient('http://example.test');

        self::assertInstanceOf(TypesEndpoint::class, $client->types());
    }

    public function testPrioritiesReturnsAPrioritiesEndpoint(): void
    {
        $client = $this->createClient('http://example.test');

        self::assertInstanceOf(PrioritiesEndpoint::class, $client->priorities());
    }

    public function testProjectsReturnsAProjectsEndpoint(): void
    {
        $client = $this->createClient('http://example.test');

        self::assertInstanceOf(ProjectsEndpoint::class, $client->projects());
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
