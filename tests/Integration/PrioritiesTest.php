<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class PrioritiesTest extends IntegrationTestCase
{
    public function testAllReturnsNonEmptyCollection(): void
    {
        $results = $this->client->priorities()->all();

        self::assertGreaterThan(0, $results->count);
        self::assertNotEmpty($results->elements);
        self::assertNotNull($results->elements[0]->name);
    }

    public function testGetReturnsSinglePriority(): void
    {
        $results = $this->client->priorities()->all();
        $firstId = $results->elements[0]->id;

        $priority = $this->client->priorities()->get($firstId);

        self::assertSame($firstId, $priority->id);
    }
}
