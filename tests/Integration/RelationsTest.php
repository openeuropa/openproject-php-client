<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\Relation;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class RelationsTest extends IntegrationTestCase
{
    public function testAllDecodesEveryRelationWithAnIntId(): void
    {
        $results = $this->client->relations()->all();

        self::assertNotEmpty($results->elements);

        foreach ($results->elements as $relation) {
            self::assertInstanceOf(Relation::class, $relation);
            self::assertGreaterThan(0, $relation->id);
        }
    }

    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->relations()->get(999999);
    }
}
