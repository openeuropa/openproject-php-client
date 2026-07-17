<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\Version;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class VersionsTest extends IntegrationTestCase
{
    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->versions()->get(999999);
    }

    public function testAllDecodesEveryVersionWithAnIntId(): void
    {
        $results = $this->client->versions()->all();

        // The seed's demo project is archived shortly after boot, which drops
        // its versions from this collection, so the collection may be empty.
        self::assertSame(count($results->elements), $results->count);

        foreach ($results->elements as $version) {
            self::assertInstanceOf(Version::class, $version);
            self::assertGreaterThan(0, $version->id);
        }
    }

    public function testAllByProjectDecodesVersionsForTheDefiningProject(): void
    {
        $results = $this->client->versions()->all();
        if ($results->elements === []) {
            self::markTestSkipped('The instance has no versions to derive a project id from.');
        }

        $projectId = $results->elements[0]->definingProject?->id();
        if ($projectId === null) {
            self::markTestSkipped('The first version has no resolvable defining project id.');
        }

        $projectResults = $this->client->versions()->allByProject($projectId);

        foreach ($projectResults->elements as $version) {
            self::assertInstanceOf(Version::class, $version);
        }
    }
}
