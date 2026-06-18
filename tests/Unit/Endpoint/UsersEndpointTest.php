<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\UsersEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\User;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(User::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
#[UsesClass(CollectionParams::class)]
final class UsersEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): UsersEndpoint
    {
        $httpFactory = new HttpFactory();

        return new UsersEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsUser(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('user.json');

        $user = $endpoint->get(1);

        self::assertSame(
            'http://example.test/api/v3/users/1',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(User::class, $user);
        self::assertSame(1, $user->id);
        self::assertSame('John Sheppard', $user->name);
    }

    public function testListPageReturnsUsersAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('users.json');

        $results = $endpoint->listPage();

        self::assertSame(
            'http://example.test/api/v3/users',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(3, $results->total);
        self::assertSame(2, $results->count);
        self::assertSame(2, $results->pageSize);
        self::assertSame(1, $results->offset);
        self::assertSame('/api/v3/users?offset=2&pageSize=2', $results->nextByOffset);
    }

    public function testListPageWithFiltersAndParamsSendsQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('users.json');

        $results = $endpoint->listPage(
            new FilterSet(new Filter('status', FilterOperator::EQUAL, ['active'])),
            new CollectionParams(pageSize: 2, sortBy: [['name', 'asc']]),
        );

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=', $uri);
        self::assertStringContainsString('pageSize=2', $uri);
        self::assertStringContainsString('sortBy=', $uri);
        self::assertInstanceOf(PageResults::class, $results);
    }

    public function testAllGeneratorPaginatesAcrossTwoPagesAndTerminates(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('users.json');
        $this->queueFixtureResponse('users-page2.json');

        $users = iterator_to_array($endpoint->all());

        self::assertCount(3, $users);
        self::assertInstanceOf(User::class, $users[0]);
        self::assertSame(1, $users[0]->id);
        self::assertSame(3, $users[2]->id);

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('offset=2', $lastUri);
    }
}
