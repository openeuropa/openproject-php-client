<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\PrioritiesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Priority;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrioritiesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Priority::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(ApiKeyAuthentication::class)]
final class PrioritiesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): PrioritiesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new PrioritiesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsPriorityAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('priority.json');

        $priority = $endpoint->get(1);

        self::assertSame(
            'http://example.test/api/v3/priorities/1',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Priority::class, $priority);
        self::assertSame(1, $priority->id);
        self::assertSame('Low', $priority->name);
    }

    public function testAllReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('priorities.json');

        $results = $endpoint->all();

        self::assertSame(
            'http://example.test/api/v3/priorities',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(4, $results->total);
        self::assertSame(4, $results->count);
        self::assertInstanceOf(Priority::class, $results->elements[0]);
        self::assertSame(1, $results->elements[0]->id);
    }
}
