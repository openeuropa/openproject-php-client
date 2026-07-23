<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\TimeEntry;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class TimeEntriesTest extends IntegrationTestCase
{
    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->timeEntries()->get(999999);
    }

    public function testListPageDecodesTimeEntries(): void
    {
        $results = $this->client->timeEntries()->listPage();

        // The seeded instance may legitimately have no time entries, so the
        // count-matches-elements check holds regardless of how many exist.
        self::assertSame(count($results->elements), $results->count);

        foreach ($results->elements as $timeEntry) {
            self::assertInstanceOf(TimeEntry::class, $timeEntry);
            self::assertGreaterThan(0, $timeEntry->id);
        }
    }

    public function testAllGeneratorYieldsEveryTimeEntry(): void
    {
        $total = $this->client->timeEntries()->listPage()->total;

        $yielded = 0;
        foreach ($this->client->timeEntries()->all() as $timeEntry) {
            self::assertGreaterThan(0, $timeEntry->id);
            $yielded++;
        }

        self::assertSame($total, $yielded);
    }
}
