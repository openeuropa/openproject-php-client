<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\TypesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Type;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Type::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(ApiKeyAuthentication::class)]
final class TypesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): TypesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new TypesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsTypeAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('type.json');

        $type = $endpoint->get(1);

        self::assertSame(
            'http://example.test/api/v3/types/1',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Type::class, $type);
        self::assertSame(1, $type->id);
        self::assertSame('Bug', $type->name);
    }

    public function testAllReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('types.json');

        $results = $endpoint->all();

        self::assertSame(
            'http://example.test/api/v3/types',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(2, $results->total);
        self::assertSame(2, $results->count);
        self::assertInstanceOf(Type::class, $results->elements[0]);
        self::assertSame(1, $results->elements[0]->id);
    }

    public function testAllByProjectReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('types.json');

        $results = $endpoint->allByProject(7);

        self::assertSame(
            'http://example.test/api/v3/projects/7/types',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(2, $results->total);
        self::assertInstanceOf(Type::class, $results->elements[0]);
    }
}
