<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class TypesTest extends IntegrationTestCase
{
    public function testAllReturnsNonEmptyCollection(): void
    {
        $results = $this->client->types()->all();

        self::assertGreaterThan(0, $results->count);
        self::assertNotEmpty($results->elements);
        self::assertNotNull($results->elements[0]->id);
        self::assertNotNull($results->elements[0]->name);
    }

    public function testGetReturnsSingleType(): void
    {
        $results = $this->client->types()->all();
        $firstId = $results->elements[0]->id;

        self::assertNotNull($firstId);

        $type = $this->client->types()->get($firstId);

        self::assertSame($firstId, $type->id);
    }
}
