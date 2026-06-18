<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackage::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
final class WorkPackageTest extends TestCase
{
    public function testDecodesWorkPackageFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/work-package.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $workPackage = WorkPackage::fromApiResponse($data);

        self::assertSame(2, $workPackage->id);
        self::assertSame(0, $workPackage->lockVersion);
        self::assertSame('Organize open source conference', $workPackage->subject);
        self::assertSame('2026-06-15', $workPackage->startDate);
        self::assertSame('2026-06-29', $workPackage->dueDate);
        self::assertNull($workPackage->date);
        self::assertSame('PT0S', $workPackage->spentTime);
        self::assertNull($workPackage->percentageDone);
        self::assertFalse($workPackage->scheduleManually);
        self::assertSame('2026-06-18T09:44:04.846Z', $workPackage->createdAt);

        self::assertInstanceOf(Formattable::class, $workPackage->description);
        self::assertSame('markdown', $workPackage->description->format);

        self::assertInstanceOf(Link::class, $workPackage->status);
        self::assertSame('In progress', $workPackage->status->title);
        self::assertSame(7, $workPackage->status->id());
        self::assertSame('Summary task', $workPackage->type?->title);
        self::assertSame('Normal', $workPackage->priority?->title);
        self::assertSame('Demo project', $workPackage->project?->title);
        self::assertSame('OpenProject Admin', $workPackage->author?->title);
        self::assertSame('OpenProject Admin', $workPackage->assignee?->title);
    }

    public function testAbsentOptionalFieldsAreNull(): void
    {
        $data = [
            '_type' => 'WorkPackage',
            'id' => 9,
            'subject' => 'Minimal work package',
        ];

        $workPackage = WorkPackage::fromApiResponse($data);

        self::assertSame(9, $workPackage->id);
        self::assertSame('Minimal work package', $workPackage->subject);
        self::assertNull($workPackage->lockVersion);
        self::assertNull($workPackage->description);
        self::assertNull($workPackage->status);
        self::assertNull($workPackage->type);
        self::assertNull($workPackage->assignee);
    }
}
