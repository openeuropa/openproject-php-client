<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ClientTest extends IntegrationTestCase
{
    public function testInfoReturnsInstanceMetadata(): void
    {
        $info = $this->client->info();

        self::assertNotSame('', $info->instanceName);
        // coreVersion is admin-only: null for a non-admin token, otherwise a
        // version string like "17.5.0". Match its shape, not a pinned value,
        // since the version bumps with every OpenProject release.
        if ($info->coreVersion !== null) {
            self::assertMatchesRegularExpression('/^\d+\.\d+/', $info->coreVersion);
        }
    }
}
