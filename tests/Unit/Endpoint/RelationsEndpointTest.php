<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\RelationsEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Relation;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelationsEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(Relation::class)]
#[UsesClass(Link::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
final class RelationsEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): RelationsEndpoint
    {
        $httpFactory = new HttpFactory();

        return new RelationsEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsRelationAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relation.json');

        $relation = $endpoint->get(650);

        self::assertSame(
            'http://example.test/api/v3/relations/650',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Relation::class, $relation);
        self::assertSame(650, $relation->id);
        self::assertSame('duplicated by', $relation->name);
    }

    public function testAllReturnsPageResultsAndSendsToCorrectUriWithNoFilters(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relations.json');

        $results = $endpoint->all();

        self::assertSame(
            'http://example.test/api/v3/relations',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(2, $results->total);
        self::assertSame(2, $results->count);
        self::assertInstanceOf(Relation::class, $results->elements[0]);
        self::assertSame(650, $results->elements[0]->id);
        self::assertSame(652, $results->elements[1]->id);
    }

    public function testAllWithFiltersSendsFiltersQueryParam(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relations.json');

        $endpoint->all(new FilterSet(new Filter('from', FilterOperator::EQUAL, ['42'])));

        $uri = (string) $this->lastRequest()->getUri();
        self::assertSame('/api/v3/relations', $this->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('filters=', $uri);
    }
}
