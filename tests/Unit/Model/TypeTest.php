<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Type::class)]
#[UsesClass(HalResource::class)]
final class TypeTest extends TestCase
{
    public function testMapsAllFieldsFromFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/type.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $type = Type::fromApiResponse($data);

        self::assertSame(1, $type->id);
        self::assertSame('Bug', $type->name);
        self::assertSame('#ff0000', $type->color);
        self::assertSame(1, $type->position);
        self::assertTrue($type->isDefault);
        self::assertFalse($type->isMilestone);
        self::assertSame('2014-05-21T08:51:20.396Z', $type->createdAt);
        self::assertSame('2014-05-21T08:51:20.396Z', $type->updatedAt);
    }

    public function testMissingFieldsProduceAllNulls(): void
    {
        $type = Type::fromApiResponse(['_type' => 'Type']);

        self::assertNull($type->id);
        self::assertNull($type->name);
        self::assertNull($type->color);
        self::assertNull($type->position);
        self::assertNull($type->isDefault);
        self::assertNull($type->isMilestone);
        self::assertNull($type->createdAt);
        self::assertNull($type->updatedAt);
    }
}
