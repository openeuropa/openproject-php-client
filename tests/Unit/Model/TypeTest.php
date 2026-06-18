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
     * A complete valid base array matching the type fixture.
     *
     * @return array<string, mixed>
     */
    private static function baseData(): array
    {
        return [
            'id' => 1,
            'name' => 'Bug',
            'color' => '#ff0000',
            'position' => 1,
            'isDefault' => true,
            'isMilestone' => false,
            'createdAt' => '2014-05-21T08:51:20.396Z',
            'updatedAt' => '2014-05-21T08:51:20.396Z',
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
        self::assertSame('2014-05-21T08:51:20.396Z', $type->createdAt);
        self::assertSame('2014-05-21T08:51:20.396Z', $type->updatedAt);
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

    public function testThrowsWhenRequiredColorMissing(): void
    {
        $data = self::baseData();
        unset($data['color']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredPositionMissing(): void
    {
        $data = self::baseData();
        unset($data['position']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredIsDefaultMissing(): void
    {
        $data = self::baseData();
        unset($data['isDefault']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredIsMilestoneMissing(): void
    {
        $data = self::baseData();
        unset($data['isMilestone']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredCreatedAtMissing(): void
    {
        $data = self::baseData();
        unset($data['createdAt']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredUpdatedAtMissing(): void
    {
        $data = self::baseData();
        unset($data['updatedAt']);

        $this->expectException(UnexpectedResponseException::class);
        Type::fromApiResponse($data);
    }
}
