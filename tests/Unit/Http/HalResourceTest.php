<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Http;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HalResource::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(UnexpectedResponseException::class)]
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

    public function testRequireIntReturnsIntValue(): void
    {
        $hal = new HalResource(['id' => 7]);

        self::assertSame(7, $hal->requireInt('id'));
    }

    public function testRequireIntThrowsWhenAbsent(): void
    {
        $hal = new HalResource([]);

        $this->expectException(UnexpectedResponseException::class);
        $hal->requireInt('id');
    }

    public function testRequireIntThrowsWhenNotAnInt(): void
    {
        $hal = new HalResource(['id' => '7']);

        $this->expectException(UnexpectedResponseException::class);
        $hal->requireInt('id');
    }

    public function testRequireStringReturnsStringValue(): void
    {
        $hal = new HalResource(['name' => 'hello']);

        self::assertSame('hello', $hal->requireString('name'));
    }

    public function testRequireStringThrowsWhenAbsent(): void
    {
        $hal = new HalResource([]);

        $this->expectException(UnexpectedResponseException::class);
        $hal->requireString('name');
    }

    public function testRequireStringThrowsWhenNotAString(): void
    {
        $hal = new HalResource(['name' => 42]);

        $this->expectException(UnexpectedResponseException::class);
        $hal->requireString('name');
    }

    public function testRequireBoolReturnsBoolValue(): void
    {
        $hal = new HalResource(['isDefault' => true]);

        self::assertSame(true, $hal->requireBool('isDefault'));
    }

    public function testRequireBoolThrowsWhenAbsent(): void
    {
        $hal = new HalResource([]);

        $this->expectException(UnexpectedResponseException::class);
        $hal->requireBool('isDefault');
    }

    public function testRequireBoolThrowsWhenNotABool(): void
    {
        $hal = new HalResource(['isDefault' => 1]);

        $this->expectException(UnexpectedResponseException::class);
        $hal->requireBool('isDefault');
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

    public function testGetDateTimeReturnsTimestampWithFractionalSeconds(): void
    {
        $hal = new HalResource(['createdAt' => '2014-05-21T08:51:20.396Z']);

        self::assertSame(
            '2014-05-21T08:51:20.396+00:00',
            $hal->getDateTime('createdAt')?->format(\DateTimeInterface::RFC3339_EXTENDED),
        );
    }

    public function testGetDateTimeReturnsTimestampWithoutFractionalSeconds(): void
    {
        $hal = new HalResource(['createdAt' => '2014-05-21T08:51:20Z']);

        self::assertSame(
            '2014-05-21T08:51:20.000+00:00',
            $hal->getDateTime('createdAt')?->format(\DateTimeInterface::RFC3339_EXTENDED),
        );
    }

    public function testGetDateTimeKeepsTheOffset(): void
    {
        $hal = new HalResource(['createdAt' => '2014-05-21T10:51:20.396+02:00']);

        self::assertSame(
            '2014-05-21T10:51:20.396+02:00',
            $hal->getDateTime('createdAt')?->format(\DateTimeInterface::RFC3339_EXTENDED),
        );
    }

    public function testGetDateTimeReturnsNullWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->getDateTime('createdAt'));
    }

    public function testGetDateTimeReturnsNullWhenNotAString(): void
    {
        $hal = new HalResource(['createdAt' => 1400662280]);

        self::assertNull($hal->getDateTime('createdAt'));
    }

    public function testGetDateTimeReturnsNullWhenNotADateTime(): void
    {
        $hal = new HalResource(['createdAt' => '2014-05-21']);

        self::assertNull($hal->getDateTime('createdAt'));
    }

    public function testGetDateTimeReturnsNullForAnImpossibleDate(): void
    {
        $hal = new HalResource(['createdAt' => '2014-02-30T08:51:20.396Z']);

        self::assertNull($hal->getDateTime('createdAt'));
    }

    public function testGetDateReturnsMidnightInTheDefaultTimeZone(): void
    {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Europe/Brussels');

        try {
            $hal = new HalResource(['dueDate' => '2026-10-20']);

            self::assertSame(
                '2026-10-20T00:00:00.000+02:00',
                $hal->getDate('dueDate')?->format(\DateTimeInterface::RFC3339_EXTENDED),
            );
        } finally {
            date_default_timezone_set($defaultTimeZone);
        }
    }

    public function testGetDateReturnsNullWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->getDate('dueDate'));
    }

    public function testGetDateReturnsNullWhenNotAString(): void
    {
        $hal = new HalResource(['dueDate' => 20261020]);

        self::assertNull($hal->getDate('dueDate'));
    }

    public function testGetDateReturnsNullWhenNotADate(): void
    {
        $hal = new HalResource(['dueDate' => '2026-10-20T10:00:00Z']);

        self::assertNull($hal->getDate('dueDate'));
    }

    public function testGetDateReturnsNullForAnImpossibleDate(): void
    {
        $hal = new HalResource(['dueDate' => '2026-02-30']);

        self::assertNull($hal->getDate('dueDate'));
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

    public function testFormattableReturnsParsedFormattableWhenPresent(): void
    {
        $hal = new HalResource([
            'description' => ['format' => 'markdown', 'raw' => 'Hello', 'html' => '<p>Hello</p>'],
        ]);

        $description = $hal->formattable('description');

        self::assertInstanceOf(Formattable::class, $description);
        self::assertSame('markdown', $description->format);
        self::assertSame('Hello', $description->raw);
        self::assertSame('<p>Hello</p>', $description->html);
    }

    public function testFormattableReturnsNullWhenAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertNull($hal->formattable('description'));
    }

    public function testFormattableReturnsNullWhenNotAnArray(): void
    {
        $hal = new HalResource(['description' => 'Hello']);

        self::assertNull($hal->formattable('description'));
    }

    public function testFormattableReturnsNullWhenFormatAbsent(): void
    {
        $hal = new HalResource(['description' => ['raw' => 'Hello']]);

        self::assertNull($hal->formattable('description'));
    }

    public function testFormattableReturnsNullWhenFormatNotAString(): void
    {
        $hal = new HalResource(['description' => ['format' => 1, 'raw' => 'Hello']]);

        self::assertNull($hal->formattable('description'));
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

    public function testLinkListReturnsListOfLinksWhenRelationIsAList(): void
    {
        $hal = new HalResource([
            '_links' => [
                'roles' => [
                    ['href' => '/api/v3/roles/4', 'title' => 'Sith Lord'],
                    ['href' => '/api/v3/roles/5'],
                ],
            ],
        ]);

        $roles = $hal->linkList('roles');

        self::assertCount(2, $roles);
        self::assertSame('/api/v3/roles/4', $roles[0]->href);
        self::assertSame('/api/v3/roles/5', $roles[1]->href);
    }

    public function testLinkListWrapsASingleLinkObjectIntoAOneElementList(): void
    {
        $hal = new HalResource([
            '_links' => [
                'roles' => ['href' => '/api/v3/roles/4', 'title' => 'Sith Lord'],
            ],
        ]);

        $roles = $hal->linkList('roles');

        self::assertCount(1, $roles);
        self::assertSame('/api/v3/roles/4', $roles[0]->href);
    }

    public function testLinkListSkipsNonArrayEntriesInTheList(): void
    {
        $hal = new HalResource([
            '_links' => [
                'roles' => [
                    ['href' => '/api/v3/roles/4'],
                    'not-an-array',
                ],
            ],
        ]);

        $roles = $hal->linkList('roles');

        self::assertCount(1, $roles);
        self::assertSame('/api/v3/roles/4', $roles[0]->href);
    }

    public function testLinkListReturnsEmptyListWhenRelationAbsent(): void
    {
        $hal = new HalResource(['_links' => []]);

        self::assertSame([], $hal->linkList('roles'));
    }

    public function testLinkListReturnsEmptyListWhenLinksAbsent(): void
    {
        $hal = new HalResource([]);

        self::assertSame([], $hal->linkList('roles'));
    }

    public function testLinkListReturnsEmptyListWhenRelationNotAnArray(): void
    {
        $hal = new HalResource(['_links' => ['roles' => 'not-an-array']]);

        self::assertSame([], $hal->linkList('roles'));
    }
}
