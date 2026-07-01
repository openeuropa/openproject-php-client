<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\ProjectInput;
use OpenEuropa\OpenProjectClient\Model\WorkPackageLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectInput::class)]
#[UsesClass(WorkPackageLinks::class)]
final class ProjectInputTest extends TestCase
{
    public function testEmptyInputProducesEmptyArray(): void
    {
        self::assertSame([], (new ProjectInput())->toArray());
    }

    public function testNameSetter(): void
    {
        $input = (new ProjectInput())->name('My project');

        self::assertSame(['name' => 'My project'], $input->toArray());
    }

    public function testIdentifierSetter(): void
    {
        $input = (new ProjectInput())->identifier('my-project');

        self::assertSame(['identifier' => 'my-project'], $input->toArray());
    }

    public function testActiveSetter(): void
    {
        $input = (new ProjectInput())->active(true);

        self::assertSame(['active' => true], $input->toArray());
    }

    public function testPublicSetter(): void
    {
        $input = (new ProjectInput())->public(false);

        self::assertSame(['public' => false], $input->toArray());
    }

    public function testDescriptionProducesRawFormattable(): void
    {
        $input = (new ProjectInput())->description('Some text');

        self::assertSame(['description' => ['raw' => 'Some text']], $input->toArray());
    }

    public function testStatusExplanationProducesRawFormattable(): void
    {
        $input = (new ProjectInput())->statusExplanation('All good');

        self::assertSame(['statusExplanation' => ['raw' => 'All good']], $input->toArray());
    }

    public function testParentSetterProducesCorrectHref(): void
    {
        $input = (new ProjectInput())->parent(42);

        self::assertSame(['_links' => ['parent' => ['href' => '/api/v3/projects/42']]], $input->toArray());
    }

    public function testStatusSetterProducesCorrectHref(): void
    {
        $input = (new ProjectInput())->status('on_track');

        self::assertSame(
            ['_links' => ['status' => ['href' => '/api/v3/project_statuses/on_track']]],
            $input->toArray()
        );
    }

    public function testCustomFieldSetsTopLevelProperty(): void
    {
        $input = (new ProjectInput())->customField(5, 'custom value');

        self::assertSame(['customField5' => 'custom value'], $input->toArray());
    }

    public function testCustomFieldLinkProducesReferenceUnderLinks(): void
    {
        $input = (new ProjectInput())->customFieldLink(26, 5, 'custom_options');

        self::assertSame(
            ['_links' => ['customField26' => ['href' => '/api/v3/custom_options/5']]],
            $input->toArray()
        );
    }

    public function testScalarAndLinkCustomFieldsCoexist(): void
    {
        $input = (new ProjectInput())
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
        $input = (new ProjectInput())->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $input->toArray());
    }

    public function testChainingReturnsSameInstance(): void
    {
        $input = new ProjectInput();
        $result = $input->name('x');

        self::assertSame($input, $result);
    }

    public function testFullInputProducesExpectedPayload(): void
    {
        $input = (new ProjectInput())
            ->name('Full project')
            ->identifier('full-project')
            ->active(true)
            ->public(false)
            ->description('A project description')
            ->statusExplanation('On schedule')
            ->parent(1)
            ->status('on_track')
            ->customField(1, 'extra');

        self::assertSame([
            'name' => 'Full project',
            'identifier' => 'full-project',
            'active' => true,
            'public' => false,
            'description' => ['raw' => 'A project description'],
            'statusExplanation' => ['raw' => 'On schedule'],
            'customField1' => 'extra',
            '_links' => [
                'parent' => ['href' => '/api/v3/projects/1'],
                'status' => ['href' => '/api/v3/project_statuses/on_track'],
            ],
        ], $input->toArray());
    }

    public function testTypedRelationshipSetterWinsOverManualLinksOnCollision(): void
    {
        $input = (new ProjectInput())
            ->set('_links', [
                'customRel' => ['href' => '/api/v3/foo/1'],
                'status' => ['href' => '/api/v3/project_statuses/off_track'],
            ])
            ->status('on_track');

        $payload = $input->toArray();

        self::assertIsArray($payload['_links']);
        /** @var array<string, array{href: string}> $links */
        $links = $payload['_links'];
        self::assertSame('/api/v3/foo/1', $links['customRel']['href']);
        self::assertSame('/api/v3/project_statuses/on_track', $links['status']['href']);
    }

    public function testFromArrayRoundTrips(): void
    {
        $data = [
            'name' => 'Seeded project',
            'active' => true,
            '_links' => [
                'parent' => ['href' => '/api/v3/projects/1'],
                'status' => ['href' => '/api/v3/project_statuses/on_track'],
            ],
        ];

        self::assertSame($data, ProjectInput::fromArray($data)->toArray());
    }

    public function testFromArrayScalarOverride(): void
    {
        self::assertSame(
            ['name' => 'Override'],
            ProjectInput::fromArray(['name' => 'Seeded'])->name('Override')->toArray()
        );
    }

    public function testFromArrayTypedLinkOverridesSeededLink(): void
    {
        $result = ProjectInput::fromArray([
            '_links' => ['status' => ['href' => '/api/v3/project_statuses/off_track']],
        ])->status('on_track')->toArray();

        self::assertSame(['_links' => ['status' => ['href' => '/api/v3/project_statuses/on_track']]], $result);
    }
}
