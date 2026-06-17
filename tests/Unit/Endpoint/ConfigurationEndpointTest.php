<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ConfigurationEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Configuration;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Configuration::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(ApiKeyAuthentication::class)]
final class ConfigurationEndpointTest extends TestCase
{
    use MockClientTrait;

    public function testGetReturnsConfigurationAndSendsToCorrectUri(): void
    {
        $httpFactory = new HttpFactory();
        $transport = new Transport(
            'http://example.test',
            new ApiKeyAuthentication('secret-token'),
            $this->createMockClient(),
            $httpFactory,
            $httpFactory,
        );

        $endpoint = new ConfigurationEndpoint($transport);
        $this->queueFixtureResponse('configuration.json');

        $config = $endpoint->get();

        self::assertSame(
            'http://example.test/api/v3/configuration',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Configuration::class, $config);
        self::assertSame(5242880, $config->maximumAttachmentFileSize);
        self::assertSame('example.com:8080', $config->hostName);
    }
}
