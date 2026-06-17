<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Status;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Status::class)]
#[UsesClass(HalResource::class)]
final class StatusTest extends TestCase
{
    public function testMapsAllFieldsFromFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/status.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $status = Status::fromApiResponse($data);

        self::assertSame(13, $status->id);
        self::assertSame('Waiting for Darth-approval', $status->name);
        self::assertFalse($status->isClosed);
        self::assertSame('#FE005D', $status->color);
        self::assertFalse($status->isDefault);
        self::assertFalse($status->isReadonly);
        self::assertFalse($status->excludedFromTotals);
        self::assertSame(66, $status->defaultDoneRatio);
        self::assertSame(4, $status->position);
    }

    public function testMissingFieldsProduceAllNulls(): void
    {
        $status = Status::fromApiResponse(['_type' => 'Status']);

        self::assertNull($status->id);
        self::assertNull($status->name);
        self::assertNull($status->isClosed);
        self::assertNull($status->color);
        self::assertNull($status->isDefault);
        self::assertNull($status->isReadonly);
        self::assertNull($status->excludedFromTotals);
        self::assertNull($status->defaultDoneRatio);
        self::assertNull($status->position);
    }
}
