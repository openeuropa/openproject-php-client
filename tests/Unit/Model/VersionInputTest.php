<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\VersionInput;
use OpenEuropa\OpenProjectClient\Model\WorkPackageLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VersionInput::class)]
#[UsesClass(WorkPackageLinks::class)]
final class VersionInputTest extends TestCase
{
    public function testEmptyInputProducesEmptyArray(): void
    {
        self::assertSame([], (new VersionInput())->toArray());
    }

    public function testNameSetter(): void
    {
        $input = (new VersionInput())->name('My version');

        self::assertSame(['name' => 'My version'], $input->toArray());
    }

    public function testStatusSetter(): void
    {
        $input = (new VersionInput())->status('open');

        self::assertSame(['status' => 'open'], $input->toArray());
    }

    public function testSharingSetter(): void
    {
        $input = (new VersionInput())->sharing('none');

        self::assertSame(['sharing' => 'none'], $input->toArray());
    }

    public function testStartDateSetter(): void
    {
        $input = (new VersionInput())->startDate('2026-02-02');

        self::assertSame(['startDate' => '2026-02-02'], $input->toArray());
    }

    public function testEndDateSetter(): void
    {
        $input = (new VersionInput())->endDate('2026-02-09');

        self::assertSame(['endDate' => '2026-02-09'], $input->toArray());
    }

    public function testDescriptionProducesRawFormattable(): void
    {
        $input = (new VersionInput())->description('x');

        self::assertSame(['description' => ['raw' => 'x']], $input->toArray());
    }

    public function testDefiningProjectSetterProducesCorrectHref(): void
    {
        $input = (new VersionInput())->definingProject(10);

        self::assertSame(
            ['_links' => ['definingProject' => ['href' => '/api/v3/projects/10']]],
            $input->toArray()
        );
    }

    public function testCustomFieldSetsTopLevelProperty(): void
    {
        $input = (new VersionInput())->customField(5, 'custom value');

        self::assertSame(['customField5' => 'custom value'], $input->toArray());
    }

    public function testCustomFieldLinkProducesReferenceUnderLinks(): void
    {
        $input = (new VersionInput())->customFieldLink(26, 5, 'custom_options');

        self::assertSame(
            ['_links' => ['customField26' => ['href' => '/api/v3/custom_options/5']]],
            $input->toArray()
        );
    }

    public function testScalarAndLinkCustomFieldsCoexist(): void
    {
        $input = (new VersionInput())
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
        $input = (new VersionInput())->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $input->toArray());
    }

    public function testChainingReturnsSameInstance(): void
    {
        $input = new VersionInput();
        $result = $input->name('x');

        self::assertSame($input, $result);
    }

    public function testFullInputProducesExpectedPayload(): void
    {
        $input = (new VersionInput())
            ->name('Full version')
            ->status('open')
            ->sharing('none')
            ->startDate('2026-02-02')
            ->endDate('2026-02-09')
            ->description('A version description')
            ->definingProject(10)
            ->customField(1, 'extra');

        self::assertSame([
            'name' => 'Full version',
            'status' => 'open',
            'sharing' => 'none',
            'startDate' => '2026-02-02',
            'endDate' => '2026-02-09',
            'description' => ['raw' => 'A version description'],
            'customField1' => 'extra',
            '_links' => [
                'definingProject' => ['href' => '/api/v3/projects/10'],
            ],
        ], $input->toArray());
    }

    public function testTypedRelationshipSetterWinsOverManualLinksOnCollision(): void
    {
        $input = (new VersionInput())
            ->set('_links', [
                'customRel' => ['href' => '/api/v3/foo/1'],
                'definingProject' => ['href' => '/api/v3/projects/1'],
            ])
            ->definingProject(10);

        $payload = $input->toArray();

        self::assertIsArray($payload['_links']);
        /** @var array<string, array{href: string}> $links */
        $links = $payload['_links'];
        self::assertSame('/api/v3/foo/1', $links['customRel']['href']);
        self::assertSame('/api/v3/projects/10', $links['definingProject']['href']);
    }

    public function testFromArrayRoundTrips(): void
    {
        $data = [
            'name' => 'Seeded version',
            'status' => 'open',
            '_links' => [
                'definingProject' => ['href' => '/api/v3/projects/10'],
            ],
        ];

        self::assertSame($data, VersionInput::fromArray($data)->toArray());
    }

    public function testFromArrayScalarOverride(): void
    {
        self::assertSame(
            ['name' => 'Override'],
            VersionInput::fromArray(['name' => 'Seeded'])->name('Override')->toArray()
        );
    }

    public function testFromArrayTypedLinkOverridesSeededLink(): void
    {
        $result = VersionInput::fromArray([
            '_links' => ['definingProject' => ['href' => '/api/v3/projects/1']],
        ])->definingProject(10)->toArray();

        self::assertSame(
            ['_links' => ['definingProject' => ['href' => '/api/v3/projects/10']]],
            $result
        );
    }
}
