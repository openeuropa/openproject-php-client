<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class StatusesTest extends IntegrationTestCase
{
    public function testAllReturnsNonEmptyCollection(): void
    {
        $results = $this->client->statuses()->all();

        self::assertGreaterThan(0, $results->count);
        self::assertNotEmpty($results->elements);
        self::assertNotNull($results->elements[0]->id);
        self::assertNotNull($results->elements[0]->name);
    }

    public function testGetReturnsSingleStatus(): void
    {
        $results = $this->client->statuses()->all();
        $firstId = $results->elements[0]->id;

        self::assertNotNull($firstId);

        $status = $this->client->statuses()->get($firstId);

        self::assertSame($firstId, $status->id);
    }
}
