<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
#[UsesClass(HalResource::class)]
final class ConfigurationTest extends TestCase
{
    public function testMapsAllFieldsFromFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/configuration.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $config = Configuration::fromApiResponse($data);

        self::assertSame(5242880, $config->maximumAttachmentFileSize);
        self::assertSame('example.com:8080', $config->hostName);
        self::assertSame([1, 10, 100], $config->perPageOptions);
        self::assertSame('hours_only', $config->durationFormat);
        self::assertSame(['aFeatureFlag', 'anotherFeatureFlag'], $config->activeFeatureFlags);
    }

    public function testEmptyInputProducesNullsAndEmptyLists(): void
    {
        $config = Configuration::fromApiResponse([]);

        self::assertNull($config->maximumAttachmentFileSize);
        self::assertNull($config->hostName);
        self::assertSame([], $config->perPageOptions);
        self::assertNull($config->durationFormat);
        self::assertSame([], $config->activeFeatureFlags);
    }
}
