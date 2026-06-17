<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ConfigurationTest extends IntegrationTestCase
{
    public function testGetReturnsConfiguration(): void
    {
        $config = $this->client->configuration()->get();

        // A live instance always reports a positive maximum attachment size,
        // proving the DTO decoded real data rather than an empty shell.
        self::assertNotNull($config->maximumAttachmentFileSize);
        self::assertGreaterThan(0, $config->maximumAttachmentFileSize);
    }
}
