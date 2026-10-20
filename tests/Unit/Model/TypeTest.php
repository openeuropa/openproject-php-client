<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Type::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class TypeTest extends TestCase
{
    /**
     * A complete valid base array with every required field present.
     *
     * @return array<string, mixed>
     */
    private static function baseData(): array
    {
        return [
            'id' => 1,
            'name' => 'Bug',
        ];
    }

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
        self::assertEquals(new \DateTimeImmutable('2014-05-21T08:51:20.396Z'), $type->createdAt);
        self::assertEquals(new \DateTimeImmutable('2014-05-21T08:51:20.396Z'), $type->updatedAt);
    }

    public function testAbsentOptionalFieldsAreNull(): void
    {
        $type = Type::fromApiResponse(self::baseData());

        self::assertNull($type->color);
        self::assertNull($type->position);
        self::assertNull($type->isDefault);
        self::assertNull($type->isMilestone);
        self::assertNull($type->createdAt);
        self::assertNull($type->updatedAt);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $data = self::baseData();
        unset($data['id']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredNameMissing(): void
    {
        $data = self::baseData();
        unset($data['name']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }
}
