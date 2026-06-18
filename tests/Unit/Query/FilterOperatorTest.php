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
    }
}
