<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Query;

use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionParams::class)]
final class CollectionParamsTest extends TestCase
{
    public function testAllFieldsSetProducesCorrectQueryParams(): void
    {
        $params = new CollectionParams(
            offset: 2,
            pageSize: 25,
            sortBy: [['name', 'asc'], ['id', 'desc']],
            groupBy: 'status',
            showSums: true,
        );

        $result = $params->toQueryParams();

        self::assertSame('2', $result['offset']);
        self::assertSame('25', $result['pageSize']);
        self::assertSame('[["name","asc"],["id","desc"]]', $result['sortBy']);
        self::assertSame('status', $result['groupBy']);
        self::assertSame('t', $result['showSums']);
    }

    public function testShowSumsFalseSerializesToF(): void
    {
        $params = new CollectionParams(showSums: false);

        self::assertSame('f', $params->toQueryParams()['showSums']);
    }

    public function testEmptyParamsProducesEmptyArray(): void
    {
        $params = new CollectionParams();

        self::assertSame([], $params->toQueryParams());
    }

    public function testWithOffsetReturnsCopyWithNewOffset(): void
    {
        $original = new CollectionParams(offset: 1, pageSize: 10, groupBy: 'type', showSums: true);
        $advanced = $original->withOffset(3);

        self::assertSame(1, $original->offset);
        self::assertSame(3, $advanced->offset);
        self::assertSame(10, $advanced->pageSize);
        self::assertSame('type', $advanced->groupBy);
        self::assertTrue($advanced->showSums);
    }
}
