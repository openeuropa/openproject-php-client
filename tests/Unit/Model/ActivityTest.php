<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Activity;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Activity::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
final class ActivityTest extends TestCase
{
    public function testDecodesActivityFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/activity.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $activity = Activity::fromApiResponse($data);

        self::assertSame(156, $activity->id);
        self::assertSame(3, $activity->version);
        self::assertSame('markdown', $activity->comment?->format);
        self::assertSame('A fresh standalone comment for the activity fixture', $activity->comment->raw);
        self::assertSame([], $activity->details);
        self::assertFalse($activity->internal);
        self::assertSame('2026-07-16T16:06:30.755Z', $activity->createdAt);
        self::assertSame('2026-07-16T16:06:30.755Z', $activity->updatedAt);

        self::assertSame(156, $activity->self?->id());
        self::assertSame(4, $activity->user?->id());
        self::assertSame(87, $activity->workPackage?->id());
    }

    public function testDecodesDetailsIntoFormattableList(): void
    {
        $activity = Activity::fromApiResponse([
            'id' => 42,
            'details' => [
                ['format' => 'custom', 'raw' => 'Status set to New', 'html' => '<strong>Status</strong> set to New'],
                ['format' => 'custom', 'raw' => 'Priority set to Normal', 'html' => '<strong>Priority</strong> set'],
            ],
        ]);

        self::assertCount(2, $activity->details);
        self::assertInstanceOf(Formattable::class, $activity->details[0]);
        self::assertSame('Status set to New', $activity->details[0]->raw);
        self::assertSame('Priority set to Normal', $activity->details[1]->raw);
    }

    public function testNonArrayDetailsElementIsSkipped(): void
    {
        $activity = Activity::fromApiResponse([
            'id' => 42,
            'details' => [
                ['format' => 'custom', 'raw' => 'Status set to New', 'html' => '<strong>Status</strong> set to New'],
                'not-an-array',
            ],
        ]);

        self::assertCount(1, $activity->details);
        self::assertSame('Status set to New', $activity->details[0]->raw);
    }

    public function testAbsentFieldsAreNullAndDetailsEmpty(): void
    {
        $activity = Activity::fromApiResponse([
            '_type' => 'Activity::Comment',
            'id' => 42,
        ]);

        self::assertSame(42, $activity->id);
        self::assertNull($activity->version);
        self::assertNull($activity->comment);
        self::assertSame([], $activity->details);
        self::assertNull($activity->internal);
        self::assertNull($activity->createdAt);
        self::assertNull($activity->updatedAt);
        self::assertNull($activity->self);
        self::assertNull($activity->user);
        self::assertNull($activity->workPackage);
    }

    public function testCommentWithoutFormatIsNull(): void
    {
        $activity = Activity::fromApiResponse([
            'id' => 42,
            'comment' => ['raw' => 'No format key'],
        ]);

        self::assertNull($activity->comment);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        Activity::fromApiResponse(['_type' => 'Activity::Comment']);
    }
}
