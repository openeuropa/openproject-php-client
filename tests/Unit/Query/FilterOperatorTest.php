<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Query;

use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterOperator::class)]
final class FilterOperatorTest extends TestCase
{
    public function testAllCasesHaveCorrectValues(): void
    {
        self::assertSame('=', FilterOperator::EQUAL->value);
        self::assertSame('!', FilterOperator::NOT_EQUAL->value);
        self::assertSame('>=', FilterOperator::GREATER_OR_EQUAL->value);
        self::assertSame('<=', FilterOperator::LESS_OR_EQUAL->value);
        self::assertSame('~', FilterOperator::CONTAINS->value);
        self::assertSame('!~', FilterOperator::NOT_CONTAINS->value);
        self::assertSame('*', FilterOperator::NOT_NULL->value);
        self::assertSame('!*', FilterOperator::IS_NULL->value);
        self::assertSame('**', FilterOperator::SEARCH->value);
        self::assertSame('=d', FilterOperator::ON_DATE->value);
        self::assertSame('<>d', FilterOperator::BETWEEN_DATES->value);
        self::assertSame('t', FilterOperator::TODAY->value);
        self::assertSame('w', FilterOperator::THIS_WEEK->value);
        self::assertSame('t-', FilterOperator::N_DAYS_AGO->value);
        self::assertSame('t+', FilterOperator::N_DAYS_FROM_NOW->value);
        self::assertSame('o', FilterOperator::OPEN->value);
        self::assertSame('c', FilterOperator::CLOSED->value);
        self::assertSame('&=', FilterOperator::EQUAL_ALL->value);
        self::assertSame('eq_with_descendants', FilterOperator::EQUAL_WITH_DESCENDANTS->value);
        self::assertSame('>t-', FilterOperator::LESS_THAN_N_DAYS_AGO->value);
        self::assertSame('<t-', FilterOperator::MORE_THAN_N_DAYS_AGO->value);
        self::assertSame('<t+', FilterOperator::LESS_THAN_N_DAYS_FROM_NOW->value);
        self::assertSame('>t+', FilterOperator::MORE_THAN_N_DAYS_FROM_NOW->value);
        self::assertSame('l', FilterOperator::LOCKED->value);
        self::assertSame('ow', FilterOperator::MANUALLY_SORTED->value);
        self::assertSame('relates', FilterOperator::RELATES->value);
        self::assertSame('precedes', FilterOperator::PRECEDES->value);
        self::assertSame('follows', FilterOperator::FOLLOWS->value);
        self::assertSame('blocks', FilterOperator::BLOCKS->value);
        self::assertSame('blocked', FilterOperator::BLOCKED->value);
        self::assertSame('duplicates', FilterOperator::DUPLICATES->value);
        self::assertSame('duplicated', FilterOperator::DUPLICATED->value);
        self::assertSame('includes', FilterOperator::INCLUDES->value);
        self::assertSame('partof', FilterOperator::PART_OF->value);
        self::assertSame('requires', FilterOperator::REQUIRES->value);
        self::assertSame('required', FilterOperator::REQUIRED->value);
        self::assertSame('parent', FilterOperator::PARENT->value);
        self::assertSame('child', FilterOperator::CHILD->value);
        self::assertSame('children', FilterOperator::CHILDREN->value);
    }
}
