<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Query;

use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
final class FilterTest extends TestCase
{
    public function testToArrayWithValues(): void
    {
        $filter = new Filter('status', FilterOperator::EQUAL, ['1', '2']);

        self::assertSame(
            ['status' => ['operator' => '=', 'values' => ['1', '2']]],
            $filter->toArray(),
        );
    }

    public function testToArrayWithNullValues(): void
    {
        $filter = new Filter('assignee', FilterOperator::IS_NULL, null);

        self::assertSame(
            ['assignee' => ['operator' => '!*', 'values' => null]],
            $filter->toArray(),
        );
    }
}
