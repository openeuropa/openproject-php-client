<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class WorkPackagesTest extends IntegrationTestCase
{
    public function testGetReturnsWorkPackageWithMatchingId(): void
    {
        // There is no list method yet (T07b), so a known seeded work package
        // id is used. A missing id skips rather than fails, so the test stays
        // valid against an instance without that work package.
        try {
            $workPackage = $this->client->workPackages()->get(2);
        } catch (NotFoundException) {
            self::markTestSkipped('Work package 2 not found in the instance.');
        }

        self::assertSame(2, $workPackage->id);
        self::assertNotNull($workPackage->subject);
    }

    public function testListPageReturnsNonNegativeTotal(): void
    {
        $results = $this->client->workPackages()->listPage();

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    public function testListPageElementsHaveIdAndSubject(): void
    {
        $results = $this->client->workPackages()->listPage();

        if (empty($results->elements)) {
            self::markTestSkipped('No work packages found in the instance.');
        }

        self::assertNotNull($results->elements[0]->id);
        self::assertNotNull($results->elements[0]->subject);
    }

    public function testAllYieldsWorkPackagesWithIdAndSubject(): void
    {
        $first = null;
        foreach ($this->client->workPackages()->all() as $workPackage) {
            $first = $workPackage;
            break;
        }

        if ($first === null) {
            self::markTestSkipped('No work packages found in the instance.');
        }

        self::assertNotNull($first->id);
        self::assertNotNull($first->subject);
    }

    public function testListByProjectYieldsWorkPackagesWithIdAndSubject(): void
    {
        $projects = $this->client->projects()->listPage();

        if (empty($projects->elements)) {
            self::markTestSkipped('No projects found in the instance.');
        }

        $projectId = $projects->elements[0]->id;
        self::assertNotNull($projectId);

        $first = null;
        foreach ($this->client->workPackages()->listByProject($projectId) as $workPackage) {
            $first = $workPackage;
            break;
        }

        if ($first === null) {
            self::markTestSkipped('No work packages found in the project.');
        }

        self::assertNotNull($first->id);
        self::assertNotNull($first->subject);
    }
}
