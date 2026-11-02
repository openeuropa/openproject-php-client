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
use OpenEuropa\OpenProjectClient\Model\RelationInput;
use OpenEuropa\OpenProjectClient\Model\RelationType;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
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
#[UsesClass(RelationInput::class)]
#[UsesClass(RelationType::class)]
#[UsesClass(Link::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
#[UsesClass(CollectionParams::class)]
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

    public function testAllGeneratorPaginatesAcrossTwoPagesAndTerminates(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relations.json');
        $this->queueFixtureResponse('relations-page2.json');

        $relations = iterator_to_array($endpoint->all());

        self::assertCount(3, $relations);
        self::assertInstanceOf(Relation::class, $relations[0]);
        self::assertSame(650, $relations[0]->id);
        self::assertSame(652, $relations[1]->id);
        self::assertSame(654, $relations[2]->id);

        $lastUri = $this->lastRequest()->getUri();
        self::assertSame('/api/v3/relations', $lastUri->getPath());
        self::assertStringContainsString('offset=2', (string) $lastUri);
    }

    public function testAllSendsFiltersAndParamsWithEveryPage(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relations.json');
        $this->queueFixtureResponse('relations-page2.json');

        iterator_to_array($endpoint->all(
            new FilterSet(new Filter('from', FilterOperator::EQUAL, ['42'])),
            new CollectionParams(pageSize: 2, sortBy: [['id', 'desc']]),
        ));

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=', $lastUri);
        self::assertStringContainsString('offset=2', $lastUri);
        self::assertStringContainsString('pageSize=2', $lastUri);
        self::assertStringContainsString('sortBy=', $lastUri);
    }

    public function testListPageReturnsRelationsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relations.json');

        $results = $endpoint->listPage();

        self::assertSame(
            'http://example.test/api/v3/relations',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(3, $results->total);
        self::assertSame(2, $results->count);
        self::assertSame(2, $results->pageSize);
        self::assertSame(1, $results->offset);
        self::assertSame('/api/v3/relations?filters=%5B%5D&offset=2&pageSize=2', $results->nextByOffset);
        self::assertInstanceOf(Relation::class, $results->elements[0]);
        self::assertSame(650, $results->elements[0]->id);
        self::assertSame(652, $results->elements[1]->id);
    }

    public function testListPageWithFiltersAndParamsSendsQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relations.json');

        $endpoint->listPage(
            new FilterSet(new Filter('from', FilterOperator::EQUAL, ['42'])),
            new CollectionParams(offset: 1, pageSize: 2, sortBy: [['id', 'desc']]),
        );

        $uri = $this->lastRequest()->getUri();
        self::assertSame('/api/v3/relations', $uri->getPath());
        self::assertStringContainsString('filters=', (string) $uri);
        self::assertStringContainsString('offset=1', (string) $uri);
        self::assertStringContainsString('pageSize=2', (string) $uri);
        self::assertStringContainsString('sortBy=', (string) $uri);
    }

    public function testCreatePostsToWorkPackageRelationsUriAndReturnsRelation(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relation.json', 201);

        $relation = $endpoint->create(108, (new RelationInput())->type(RelationType::RELATES)->to(78));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/api/v3/work_packages/108/relations', $request->getUri()->getPath());
        $body = (string) $request->getBody();
        self::assertStringContainsString('"type":"relates"', $body);
        self::assertStringContainsString('"href":"\/api\/v3\/work_packages\/78"', $body);
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertInstanceOf(Relation::class, $relation);
        self::assertSame(650, $relation->id);
    }

    public function testUpdatePatchesToRelationUriAndReturnsRelation(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('relation.json');

        $relation = $endpoint->update(650, (new RelationInput())->description('updated'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame('/api/v3/relations/650', $request->getUri()->getPath());
        self::assertStringContainsString('"description":"updated"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertInstanceOf(Relation::class, $relation);
        self::assertSame(650, $relation->id);
    }

    public function testDeleteSendsDeleteToRelationUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueJsonResponse('', 204);

        $endpoint->delete(650);

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/relations/650',
            (string) $request->getUri(),
        );
    }
}
