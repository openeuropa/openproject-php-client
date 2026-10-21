<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\CustomFields;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\TimeEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntry::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(CustomFields::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
final class TimeEntryTest extends TestCase
{
    public function testDecodesTimeEntryFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/time-entry.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $timeEntry = TimeEntry::fromApiResponse($data);

        self::assertSame(42, $timeEntry->id);
        self::assertInstanceOf(Formattable::class, $timeEntry->comment);
        self::assertSame('plain', $timeEntry->comment->format);
        self::assertSame('The force shall set me free.', $timeEntry->comment->raw);
        self::assertSame('<p>The force shall set me free.</p>', $timeEntry->comment->html);
        self::assertEquals(new \DateTimeImmutable('2023-01-11'), $timeEntry->spentOn);
        self::assertEquals(new \DateInterval('PT4H'), $timeEntry->hours);
        self::assertNull($timeEntry->ongoing);
        self::assertEquals(new \DateTimeImmutable('2023-01-11T09:58:00.000Z'), $timeEntry->startTime);
        self::assertEquals(new \DateTimeImmutable('2023-01-11T13:58:00.000Z'), $timeEntry->endTime);
        self::assertEquals(new \DateTimeImmutable('2023-01-11T13:58:24.927Z'), $timeEntry->createdAt);
        self::assertEquals(new \DateTimeImmutable('2023-01-11T13:58:24.927Z'), $timeEntry->updatedAt);
        self::assertSame(42, $timeEntry->self?->id());
        self::assertSame(11, $timeEntry->project?->id());
        self::assertSame(77, $timeEntry->entity?->id());
        self::assertSame(3, $timeEntry->user?->id());
        self::assertSame(1, $timeEntry->activity?->id());
        self::assertSame([], $timeEntry->customFields->values);
        self::assertSame([], $timeEntry->customFields->links);
    }

    public function testAbsentFieldsAreNull(): void
    {
        $timeEntry = TimeEntry::fromApiResponse([
            '_type' => 'TimeEntry',
            'id' => 11,
        ]);

        self::assertSame(11, $timeEntry->id);
        self::assertNull($timeEntry->comment);
        self::assertNull($timeEntry->spentOn);
        self::assertNull($timeEntry->hours);
        self::assertNull($timeEntry->ongoing);
        self::assertNull($timeEntry->startTime);
        self::assertNull($timeEntry->endTime);
        self::assertNull($timeEntry->createdAt);
        self::assertNull($timeEntry->updatedAt);
        self::assertNull($timeEntry->self);
        self::assertNull($timeEntry->project);
        self::assertNull($timeEntry->entity);
        self::assertNull($timeEntry->user);
        self::assertNull($timeEntry->activity);
    }

    public function testCommentWithoutFormatIsNull(): void
    {
        $timeEntry = TimeEntry::fromApiResponse([
            '_type' => 'TimeEntry',
            'id' => 11,
            'comment' => ['raw' => 'No format key'],
        ]);

        self::assertNull($timeEntry->comment);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        TimeEntry::fromApiResponse(['_type' => 'TimeEntry']);
    }
}
