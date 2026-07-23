<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\TimeEntryInput;
use OpenEuropa\OpenProjectClient\Model\WorkPackageLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntryInput::class)]
#[UsesClass(WorkPackageLinks::class)]
final class TimeEntryInputTest extends TestCase
{
    public function testEmptyInputProducesEmptyArray(): void
    {
        self::assertSame([], (new TimeEntryInput())->toArray());
    }

    public function testSpentOnSetter(): void
    {
        $input = (new TimeEntryInput())->spentOn('2026-07-23');

        self::assertSame(['spentOn' => '2026-07-23'], $input->toArray());
    }

    public function testHoursSetter(): void
    {
        $input = (new TimeEntryInput())->hours('PT2H');

        self::assertSame(['hours' => 'PT2H'], $input->toArray());
    }

    public function testOngoingSetter(): void
    {
        $input = (new TimeEntryInput())->ongoing(true);

        self::assertSame(['ongoing' => true], $input->toArray());
    }

    public function testStartTimeSetter(): void
    {
        $input = (new TimeEntryInput())->startTime('2026-07-23T09:00:00.000Z');

        self::assertSame(['startTime' => '2026-07-23T09:00:00.000Z'], $input->toArray());
    }

    public function testEndTimeSetter(): void
    {
        $input = (new TimeEntryInput())->endTime('2026-07-23T11:00:00.000Z');

        self::assertSame(['endTime' => '2026-07-23T11:00:00.000Z'], $input->toArray());
    }

    public function testCommentProducesRawFormattable(): void
    {
        $input = (new TimeEntryInput())->comment('x');

        self::assertSame(['comment' => ['raw' => 'x']], $input->toArray());
    }

    public function testProjectSetterProducesCorrectHref(): void
    {
        $input = (new TimeEntryInput())->project(11);

        self::assertSame(
            ['_links' => ['project' => ['href' => '/api/v3/projects/11']]],
            $input->toArray()
        );
    }

    public function testWorkPackageSetterProducesEntityHref(): void
    {
        $input = (new TimeEntryInput())->workPackage(77);

        self::assertSame(
            ['_links' => ['entity' => ['href' => '/api/v3/work_packages/77']]],
            $input->toArray()
        );
    }

    public function testUserSetterProducesCorrectHref(): void
    {
        $input = (new TimeEntryInput())->user(3);

        self::assertSame(
            ['_links' => ['user' => ['href' => '/api/v3/users/3']]],
            $input->toArray()
        );
    }

    public function testActivitySetterProducesCorrectHref(): void
    {
        $input = (new TimeEntryInput())->activity(1);

        self::assertSame(
            ['_links' => ['activity' => ['href' => '/api/v3/time_entries/activities/1']]],
            $input->toArray()
        );
    }

    public function testCustomFieldSetsTopLevelProperty(): void
    {
        $input = (new TimeEntryInput())->customField(5, 'custom value');

        self::assertSame(['customField5' => 'custom value'], $input->toArray());
    }

    public function testCustomFieldLinkProducesReferenceUnderLinks(): void
    {
        $input = (new TimeEntryInput())->customFieldLink(26, 5, 'custom_options');

        self::assertSame(
            ['_links' => ['customField26' => ['href' => '/api/v3/custom_options/5']]],
            $input->toArray()
        );
    }

    public function testScalarAndLinkCustomFieldsCoexist(): void
    {
        $input = (new TimeEntryInput())
            ->customField(1, 'plain value')
            ->customFieldLink(2, 9, 'users');

        self::assertSame([
            'customField1' => 'plain value',
            '_links' => [
                'customField2' => ['href' => '/api/v3/users/9'],
            ],
        ], $input->toArray());
    }

    public function testSetSetsArbitraryTopLevelProperty(): void
    {
        $input = (new TimeEntryInput())->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $input->toArray());
    }

    public function testChainingReturnsSameInstance(): void
    {
        $input = new TimeEntryInput();
        $result = $input->spentOn('2026-07-23');

        self::assertSame($input, $result);
    }

    public function testFullInputProducesExpectedPayload(): void
    {
        $input = (new TimeEntryInput())
            ->spentOn('2026-07-23')
            ->hours('PT2H')
            ->ongoing(false)
            ->startTime('2026-07-23T09:00:00.000Z')
            ->endTime('2026-07-23T11:00:00.000Z')
            ->comment('A time entry comment')
            ->project(11)
            ->workPackage(77)
            ->user(3)
            ->activity(1)
            ->customField(1, 'extra');

        self::assertSame([
            'spentOn' => '2026-07-23',
            'hours' => 'PT2H',
            'ongoing' => false,
            'startTime' => '2026-07-23T09:00:00.000Z',
            'endTime' => '2026-07-23T11:00:00.000Z',
            'comment' => ['raw' => 'A time entry comment'],
            'customField1' => 'extra',
            '_links' => [
                'project' => ['href' => '/api/v3/projects/11'],
                'entity' => ['href' => '/api/v3/work_packages/77'],
                'user' => ['href' => '/api/v3/users/3'],
                'activity' => ['href' => '/api/v3/time_entries/activities/1'],
            ],
        ], $input->toArray());
    }

    public function testTypedRelationshipSetterWinsOverManualLinksOnCollision(): void
    {
        $input = (new TimeEntryInput())
            ->set('_links', [
                'customRel' => ['href' => '/api/v3/foo/1'],
                'project' => ['href' => '/api/v3/projects/1'],
            ])
            ->project(11);

        $payload = $input->toArray();

        self::assertIsArray($payload['_links']);
        /** @var array<string, array{href: string}> $links */
        $links = $payload['_links'];
        self::assertSame('/api/v3/foo/1', $links['customRel']['href']);
        self::assertSame('/api/v3/projects/11', $links['project']['href']);
    }

    public function testFromArrayRoundTrips(): void
    {
        $data = [
            'spentOn' => '2026-07-23',
            'hours' => 'PT2H',
            '_links' => [
                'project' => ['href' => '/api/v3/projects/11'],
            ],
        ];

        self::assertSame($data, TimeEntryInput::fromArray($data)->toArray());
    }

    public function testFromArrayScalarOverride(): void
    {
        self::assertSame(
            ['hours' => 'PT4H'],
            TimeEntryInput::fromArray(['hours' => 'PT2H'])->hours('PT4H')->toArray()
        );
    }

    public function testFromArrayTypedLinkOverridesSeededLink(): void
    {
        $result = TimeEntryInput::fromArray([
            '_links' => ['project' => ['href' => '/api/v3/projects/1']],
        ])->project(11)->toArray();

        self::assertSame(
            ['_links' => ['project' => ['href' => '/api/v3/projects/11']]],
            $result
        );
    }
}
