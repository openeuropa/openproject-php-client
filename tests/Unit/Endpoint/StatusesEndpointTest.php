<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\StatusesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Status;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Status::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(ApiKeyAuthentication::class)]
final class StatusesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): StatusesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new StatusesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsStatusAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('status.json');

        $status = $endpoint->get(13);

        self::assertSame(
            'http://example.test/api/v3/statuses/13',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Status::class, $status);
        self::assertSame(13, $status->id);
        self::assertSame('Waiting for Darth-approval', $status->name);
    }

    public function testAllReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('statuses.json');

        $results = $endpoint->all();

        self::assertSame(
            'http://example.test/api/v3/statuses',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(6, $results->total);
        self::assertSame(6, $results->count);
        self::assertInstanceOf(Status::class, $results->elements[0]);
        self::assertSame(1, $results->elements[0]->id);
    }
}
