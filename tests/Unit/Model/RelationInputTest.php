<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\RelationInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelationInput::class)]
final class RelationInputTest extends TestCase
{
    public function testEmptyInputProducesEmptyArray(): void
    {
        self::assertSame([], (new RelationInput())->toArray());
    }

    public function testTypeSetter(): void
    {
        $input = (new RelationInput())->type('relates');

        self::assertSame(['type' => 'relates'], $input->toArray());
    }

    public function testDescriptionSetterProducesPlainString(): void
    {
        $input = (new RelationInput())->description('Some text');

        self::assertSame(['description' => 'Some text'], $input->toArray());
    }

    public function testLagSetter(): void
    {
        $input = (new RelationInput())->lag(3);

        self::assertSame(['lag' => 3], $input->toArray());
    }

    public function testToSetterProducesCorrectHref(): void
    {
        $input = (new RelationInput())->to(78);

        self::assertSame(['_links' => ['to' => ['href' => '/api/v3/work_packages/78']]], $input->toArray());
    }

    public function testSetSetsArbitraryTopLevelProperty(): void
    {
        $input = (new RelationInput())->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $input->toArray());
    }

    public function testChainingReturnsSameInstance(): void
    {
        $input = new RelationInput();
        $result = $input->type('relates');

        self::assertSame($input, $result);
    }

    public function testFullInputProducesExpectedPayload(): void
    {
        $input = (new RelationInput())
            ->type('follows')
            ->description('A relation description')
            ->lag(2)
            ->to(78);

        self::assertSame([
            'type' => 'follows',
            'description' => 'A relation description',
            'lag' => 2,
            '_links' => [
                'to' => ['href' => '/api/v3/work_packages/78'],
            ],
        ], $input->toArray());
    }

    public function testTypedRelationshipSetterWinsOverManualLinksOnCollision(): void
    {
        $input = (new RelationInput())
            ->set('_links', [
                'customRel' => ['href' => '/api/v3/foo/1'],
                'to' => ['href' => '/api/v3/work_packages/99'],
            ])
            ->to(78);

        $payload = $input->toArray();

        self::assertIsArray($payload['_links']);
        /** @var array<string, array{href: string}> $links */
        $links = $payload['_links'];
        self::assertSame('/api/v3/foo/1', $links['customRel']['href']);
        self::assertSame('/api/v3/work_packages/78', $links['to']['href']);
    }

    public function testFromArrayRoundTrips(): void
    {
        $data = [
            'type' => 'relates',
            '_links' => [
                'to' => ['href' => '/api/v3/work_packages/78'],
            ],
        ];

        self::assertSame($data, RelationInput::fromArray($data)->toArray());
    }

    public function testFromArrayScalarOverride(): void
    {
        self::assertSame(
            ['type' => 'override'],
            RelationInput::fromArray(['type' => 'relates'])->type('override')->toArray()
        );
    }

    public function testFromArrayTypedLinkOverridesSeededLink(): void
    {
        $result = RelationInput::fromArray([
            '_links' => ['to' => ['href' => '/api/v3/work_packages/1']],
        ])->to(78)->toArray();

        self::assertSame(['_links' => ['to' => ['href' => '/api/v3/work_packages/78']]], $result);
    }
}
