<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\CategoriesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Category;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CategoriesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Category::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(Link::class)]
final class CategoriesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): CategoriesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new CategoriesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsCategoryAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('category.json');

        $category = $endpoint->get(10);

        self::assertSame(
            'http://example.test/api/v3/categories/10',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Category::class, $category);
        self::assertSame(10, $category->id);
        self::assertSame('Foo', $category->name);
    }

    public function testAllByProjectReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('categories.json');

        $results = $endpoint->allByProject(11);

        self::assertSame(
            'http://example.test/api/v3/projects/11/categories',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(2, $results->total);
        self::assertSame(2, $results->count);
        self::assertInstanceOf(Category::class, $results->elements[0]);
        self::assertSame(10, $results->elements[0]->id);
    }
}
