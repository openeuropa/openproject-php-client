<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\CustomFields;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackage::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(CustomFields::class)]
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
        self::assertEquals(new \DateTimeImmutable('2026-06-18T09:44:04.846Z'), $workPackage->createdAt);

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

        // Top-level simple custom fields.
        self::assertSame('Text custom field value', $workPackage->customFields->values['customField35']);
        self::assertTrue($workPackage->customFields->values['customField34']);
        self::assertSame(42, $workPackage->customFields->values['customField30']);

        // Reference custom field under _links.
        self::assertCount(1, $workPackage->customFields->links['customField26']);
        self::assertSame('/api/v3/users/4', $workPackage->customFields->links['customField26'][0]->href);
        self::assertSame('OpenProject Admin', $workPackage->customFields->links['customField26'][0]->title);

        // The _links.customFields HTML settings link (plural, no digit) must NOT appear in the bag.
        self::assertArrayNotHasKey('customFields', $workPackage->customFields->links);
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
        self::assertSame([], $workPackage->customFields->values);
        self::assertSame([], $workPackage->customFields->links);
    }

    public function testThrowsWhenRequiredSubjectMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        WorkPackage::fromApiResponse(['id' => 9]);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        WorkPackage::fromApiResponse(['subject' => 'Minimal work package']);
    }
}
