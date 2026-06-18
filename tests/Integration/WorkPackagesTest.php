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
}
