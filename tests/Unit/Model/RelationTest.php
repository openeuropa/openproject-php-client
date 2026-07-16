<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\Relation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Relation::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Link::class)]
final class RelationTest extends TestCase
{
    public function testDecodesRelationFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/relation.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $relation = Relation::fromApiResponse($data);

        self::assertSame(650, $relation->id);
        self::assertSame('duplicated by', $relation->name);
        self::assertSame('duplicated', $relation->type);
        self::assertSame('duplicates', $relation->reverseType);
        self::assertSame('Those are the same... stupid Stormtroopers!', $relation->description);
        self::assertNull($relation->lag);
        self::assertSame(650, $relation->self?->id());
        self::assertSame(108, $relation->from?->id());
        self::assertSame(78, $relation->to?->id());
    }

    public function testLagIsMappedWhenPresent(): void
    {
        $relation = Relation::fromApiResponse(['id' => 42, 'lag' => 3]);

        self::assertSame(3, $relation->lag);
    }

    public function testAbsentFieldsAreNull(): void
    {
        $relation = Relation::fromApiResponse([
            '_type' => 'Relation',
            'id' => 652,
        ]);

        self::assertSame(652, $relation->id);
        self::assertNull($relation->name);
        self::assertNull($relation->type);
        self::assertNull($relation->reverseType);
        self::assertNull($relation->description);
        self::assertNull($relation->lag);
        self::assertNull($relation->self);
        self::assertNull($relation->from);
        self::assertNull($relation->to);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        Relation::fromApiResponse(['_type' => 'Relation']);
    }
}
