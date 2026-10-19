<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Priority;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Priority::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class PriorityTest extends TestCase
{
    public function testMapsAllFieldsFromFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/priority.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $priority = Priority::fromApiResponse($data);

        self::assertSame(1, $priority->id);
        self::assertSame('Low', $priority->name);
        self::assertSame(1, $priority->position);
        self::assertFalse($priority->isDefault);
        self::assertTrue($priority->isActive);
    }

    public function testMissingOptionalFieldsProduceNulls(): void
    {
        $priority = Priority::fromApiResponse(['_type' => 'Priority', 'id' => 5, 'name' => 'Low']);

        self::assertSame(5, $priority->id);
        self::assertSame('Low', $priority->name);
        self::assertNull($priority->position);
        self::assertNull($priority->isDefault);
        self::assertNull($priority->isActive);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        Priority::fromApiResponse(['_type' => 'Priority', 'name' => 'Low']);
    }

    public function testThrowsWhenRequiredNameMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        Priority::fromApiResponse(['_type' => 'Priority', 'id' => 5]);
    }
}
