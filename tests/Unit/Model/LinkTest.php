<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Link::class)]
final class LinkTest extends TestCase
{
    public function testMapsHrefAndTitle(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/work_packages/42', 'title' => 'My task']);

        self::assertSame('/api/v3/work_packages/42', $link->href);
        self::assertSame('My task', $link->title);
    }

    public function testHrefIsNullWhenAbsent(): void
    {
        $link = Link::fromApiResponse(['title' => 'Only title']);

        self::assertNull($link->href);
    }

    public function testTitleIsNullWhenAbsent(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/configuration']);

        self::assertNull($link->title);
    }

    public function testHrefIsNullWhenNotAString(): void
    {
        $link = Link::fromApiResponse(['href' => 42]);

        self::assertNull($link->href);
    }

    public function testTitleIsNullWhenNotAString(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/configuration', 'title' => true]);

        self::assertNull($link->title);
    }

    public function testIdExtractsNumericLastSegmentForWorkPackages(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/work_packages/42']);

        self::assertSame(42, $link->id());
    }

    public function testIdExtractsNumericLastSegmentForPortfolios(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/portfolios/3']);

        self::assertSame(3, $link->id());
    }

    public function testIdReturnsNullForNonNumericLastSegment(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/configuration']);

        self::assertNull($link->id());
    }

    public function testIdToleratesTrailingSlash(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/projects/5/']);

        self::assertSame(5, $link->id());
    }

    public function testIdReturnsNullWhenHrefIsNull(): void
    {
        $link = Link::fromApiResponse([]);

        self::assertNull($link->id());
    }

    public function testIdReturnsNullForTemplatedSegment(): void
    {
        $link = Link::fromApiResponse(['href' => '/api/v3/work_packages/{id}']);

        self::assertNull($link->id());
    }
}
