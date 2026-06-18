<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Query;

use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
final class FilterSetTest extends TestCase
{
    public function testEmptySetSerializesToEmptyJsonArray(): void
    {
        $set = new FilterSet();

        self::assertSame('[]', $set->toJson());
    }

    public function testSingleFilterSerializesCorrectly(): void
    {
        $set = new FilterSet(new Filter('status', FilterOperator::EQUAL, ['1']));

        self::assertSame('[{"status":{"operator":"=","values":["1"]}}]', $set->toJson());
    }

    public function testMultipleFiltersSerializeAsArray(): void
    {
        $set = new FilterSet(
            new Filter('status', FilterOperator::EQUAL, ['1']),
            new Filter('assignee', FilterOperator::IS_NULL, null),
        );

        self::assertSame(
            '[{"status":{"operator":"=","values":["1"]}},{"assignee":{"operator":"!*","values":null}}]',
            $set->toJson(),
        );
    }
}
