<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Http;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HalResource::class)]
#[UsesClass(Link::class)]
final class HalResourceTest extends TestCase
{
    public function testGetStringReturnsStringValue(): void
    {
        $hal = new HalResource(['name' => 'hello']);

        self::assertSame('hello', $hal->getString('name'));
    }

    public function testGetStringReturnsNullWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->getString('name'));
    }

    public function testGetStringReturnsNullWhenNotAString(): void
    {
        $hal = new HalResource(['name' => 42]);

        self::assertNull($hal->getString('name'));
    }

    public function testGetIntReturnsIntValue(): void
    {
        $hal = new HalResource(['count' => 10]);

        self::assertSame(10, $hal->getInt('count'));
    }

    public function testGetIntReturnsNullWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->getInt('count'));
    }

    public function testGetIntReturnsNullWhenNotAnInt(): void
    {
        $hal = new HalResource(['count' => '10']);

        self::assertNull($hal->getInt('count'));
    }

    public function testGetBoolReturnsBoolValue(): void
    {
        $hal = new HalResource(['isClosed' => true]);

        self::assertSame(true, $hal->getBool('isClosed'));
    }

    public function testGetBoolReturnsNullWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->getBool('isClosed'));
    }

    public function testGetBoolReturnsNullWhenNotABool(): void
    {
        $hal = new HalResource(['isClosed' => 1]);

        self::assertNull($hal->getBool('isClosed'));
    }

    public function testGetArrayReturnsArrayValue(): void
    {
        $hal = new HalResource(['items' => [1, 2, 3]]);

        self::assertSame([1, 2, 3], $hal->getArray('items'));
    }

    public function testGetArrayReturnsEmptyArrayWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertSame([], $hal->getArray('items'));
    }

    public function testGetArrayReturnsEmptyArrayWhenNotAnArray(): void
    {
        $hal = new HalResource(['items' => 'not-an-array']);

        self::assertSame([], $hal->getArray('items'));
    }

    public function testLinkReturnsParsedLinkWhenPresent(): void
    {
        $hal = new HalResource([
            '_links' => [
                'self' => ['href' => '/api/v3/configuration', 'title' => null],
            ],
        ]);

        $link = $hal->link('self');

        self::assertInstanceOf(Link::class, $link);
        self::assertSame('/api/v3/configuration', $link->href);
    }

    public function testLinkReturnsNullWhenRelationAbsent(): void
    {
        $hal = new HalResource(['_links' => []]);

        self::assertNull($hal->link('self'));
    }

    public function testLinkReturnsNullWhenLinksAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->link('self'));
    }

    public function testLinkReturnsNullWhenEntryNotAnArray(): void
    {
        $hal = new HalResource(['_links' => ['self' => 'not-an-array']]);

        self::assertNull($hal->link('self'));
    }
}
