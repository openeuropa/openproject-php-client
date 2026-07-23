<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\Category;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class CategoriesTest extends IntegrationTestCase
{
    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->categories()->get(999999);
    }

    public function testAllByProjectDecodesCategoriesForTheActiveProject(): void
    {
        $results = $this->client->categories()->allByProject($this->firstActiveProjectId());
        if ($results->elements === []) {
            self::markTestSkipped('The active project has no categories.');
        }

        foreach ($results->elements as $category) {
            self::assertInstanceOf(Category::class, $category);
            self::assertGreaterThan(0, $category->id);
            self::assertNotNull($category->project);
        }
    }

    public function testGetReturnsCategoryFromActiveProject(): void
    {
        $results = $this->client->categories()->allByProject($this->firstActiveProjectId());
        if ($results->elements === []) {
            self::markTestSkipped('The active project has no categories.');
        }

        $first = $results->elements[0];
        $fetched = $this->client->categories()->get($first->id);

        self::assertSame($first->id, $fetched->id);
    }
}
