<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ProjectsTest extends IntegrationTestCase
{
    public function testListPageReturnsNonNegativeTotal(): void
    {
        $results = $this->client->projects()->listPage();

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    public function testListPageElementsHaveIdAndName(): void
    {
        $results = $this->client->projects()->listPage();

        if (empty($results->elements)) {
            self::markTestSkipped('No projects found in the instance.');
        }

        self::assertNotNull($results->elements[0]->id);
        self::assertNotNull($results->elements[0]->name);
    }

    public function testAllGeneratorReturnsIterable(): void
    {
        $projects = iterator_to_array($this->client->projects()->all());

        if (empty($projects)) {
            self::markTestSkipped('No projects found in the instance.');
        }

        self::assertNotNull($projects[0]->id);
        self::assertNotNull($projects[0]->name);
    }

    public function testGetReturnsProjectWithMatchingId(): void
    {
        $results = $this->client->projects()->listPage();

        if (empty($results->elements)) {
            self::markTestSkipped('No projects found in the instance.');
        }

        $firstId = $results->elements[0]->id;
        self::assertNotNull($firstId);

        $project = $this->client->projects()->get($firstId);

        self::assertSame($firstId, $project->id);
    }
}
