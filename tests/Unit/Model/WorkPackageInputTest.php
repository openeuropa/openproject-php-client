<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\WorkPackageInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackageInput::class)]
final class WorkPackageInputTest extends TestCase
{
    public function testEmptyInputProducesEmptyArray(): void
    {
        self::assertSame([], (new WorkPackageInput())->toArray());
    }

    public function testSubjectSetter(): void
    {
        $input = (new WorkPackageInput())->subject('My task');

        self::assertSame(['subject' => 'My task'], $input->toArray());
    }

    public function testDescriptionProducesRawFormattable(): void
    {
        $input = (new WorkPackageInput())->description('Some text');

        self::assertSame(['description' => ['raw' => 'Some text']], $input->toArray());
    }

    public function testStartDateSetter(): void
    {
        $input = (new WorkPackageInput())->startDate('2024-01-15');

        self::assertSame(['startDate' => '2024-01-15'], $input->toArray());
    }

    public function testDueDateSetter(): void
    {
        $input = (new WorkPackageInput())->dueDate('2024-02-28');

        self::assertSame(['dueDate' => '2024-02-28'], $input->toArray());
    }

    public function testEstimatedTimeSetter(): void
    {
        $input = (new WorkPackageInput())->estimatedTime('PT8H');

        self::assertSame(['estimatedTime' => 'PT8H'], $input->toArray());
    }

    public function testScheduleManuallySetter(): void
    {
        $input = (new WorkPackageInput())->scheduleManually(true);

        self::assertSame(['scheduleManually' => true], $input->toArray());
    }

    public function testPercentageDoneSetter(): void
    {
        $input = (new WorkPackageInput())->percentageDone(50);

        self::assertSame(['percentageDone' => 50], $input->toArray());
    }

    public function testTypeSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->type(3);

        self::assertSame(['_links' => ['type' => ['href' => '/api/v3/types/3']]], $input->toArray());
    }

    public function testStatusSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->status(2);

        self::assertSame(['_links' => ['status' => ['href' => '/api/v3/statuses/2']]], $input->toArray());
    }

    public function testPrioritySetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->priority(1);

        self::assertSame(['_links' => ['priority' => ['href' => '/api/v3/priorities/1']]], $input->toArray());
    }

    public function testProjectSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->project(5);

        self::assertSame(['_links' => ['project' => ['href' => '/api/v3/projects/5']]], $input->toArray());
    }

    public function testAssigneeSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->assignee(7);

        self::assertSame(['_links' => ['assignee' => ['href' => '/api/v3/users/7']]], $input->toArray());
    }

    public function testResponsibleSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->responsible(8);

        self::assertSame(['_links' => ['responsible' => ['href' => '/api/v3/users/8']]], $input->toArray());
    }

    public function testParentSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->parent(10);

        self::assertSame(['_links' => ['parent' => ['href' => '/api/v3/work_packages/10']]], $input->toArray());
    }

    public function testVersionSetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->version(4);

        self::assertSame(['_links' => ['version' => ['href' => '/api/v3/versions/4']]], $input->toArray());
    }

    public function testCategorySetterProducesCorrectHref(): void
    {
        $input = (new WorkPackageInput())->category(6);

        self::assertSame(['_links' => ['category' => ['href' => '/api/v3/categories/6']]], $input->toArray());
    }

    public function testCustomFieldSetsTopLevelProperty(): void
    {
        $input = (new WorkPackageInput())->customField(5, 'custom value');

        self::assertSame(['customField5' => 'custom value'], $input->toArray());
    }

    public function testSetSetsArbitraryTopLevelProperty(): void
    {
        $input = (new WorkPackageInput())->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $input->toArray());
    }

    public function testChainingReturnsSameInstance(): void
    {
        $input = new WorkPackageInput();
        $result = $input->subject('x');

        self::assertSame($input, $result);
    }

    public function testFullInputProducesExpectedPayload(): void
    {
        $input = (new WorkPackageInput())
            ->subject('Full task')
            ->description('Some description')
            ->startDate('2024-01-01')
            ->dueDate('2024-01-31')
            ->estimatedTime('PT4H')
            ->scheduleManually(false)
            ->percentageDone(25)
            ->type(1)
            ->status(2)
            ->project(3)
            ->customField(1, 'extra');

        self::assertSame([
            'subject' => 'Full task',
            'description' => ['raw' => 'Some description'],
            'startDate' => '2024-01-01',
            'dueDate' => '2024-01-31',
            'estimatedTime' => 'PT4H',
            'scheduleManually' => false,
            'percentageDone' => 25,
            'customField1' => 'extra',
            '_links' => [
                'type' => ['href' => '/api/v3/types/1'],
                'status' => ['href' => '/api/v3/statuses/2'],
                'project' => ['href' => '/api/v3/projects/3'],
            ],
        ], $input->toArray());
    }

    public function testManualLinksPreservedWhenRelationshipSetterAlsoUsed(): void
    {
        $input = (new WorkPackageInput())
            ->set('_links', ['customRel' => ['href' => '/api/v3/foo/1']])
            ->type(2);

        $payload = $input->toArray();

        self::assertIsArray($payload['_links']);
        /** @var array<string, array{href: string}> $links */
        $links = $payload['_links'];
        self::assertSame('/api/v3/foo/1', $links['customRel']['href']);
        self::assertSame('/api/v3/types/2', $links['type']['href']);
    }
}
