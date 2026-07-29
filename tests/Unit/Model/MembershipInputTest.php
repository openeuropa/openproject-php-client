<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\MembershipInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MembershipInput::class)]
final class MembershipInputTest extends TestCase
{
    public function testEmptyInputProducesEmptyArray(): void
    {
        self::assertSame([], (new MembershipInput())->toArray());
    }

    public function testUserSetterProducesPrincipalHref(): void
    {
        $input = (new MembershipInput())->user(14);

        self::assertSame(
            ['_links' => ['principal' => ['href' => '/api/v3/users/14']]],
            $input->toArray()
        );
    }

    public function testProjectSetterProducesCorrectHref(): void
    {
        $input = (new MembershipInput())->project(21);

        self::assertSame(
            ['_links' => ['project' => ['href' => '/api/v3/projects/21']]],
            $input->toArray()
        );
    }

    public function testRolesSetterProducesListOfHrefs(): void
    {
        $input = (new MembershipInput())->roles(4, 6);

        self::assertSame(
            [
                '_links' => [
                    'roles' => [
                        ['href' => '/api/v3/roles/4'],
                        ['href' => '/api/v3/roles/6'],
                    ],
                ],
            ],
            $input->toArray()
        );
    }

    public function testRolesSetterReplacesPreviousRoles(): void
    {
        $input = (new MembershipInput())->roles(4)->roles(5, 6);

        self::assertSame(
            [
                '_links' => [
                    'roles' => [
                        ['href' => '/api/v3/roles/5'],
                        ['href' => '/api/v3/roles/6'],
                    ],
                ],
            ],
            $input->toArray()
        );
    }

    public function testRolesSetterWithNoIdsProducesEmptyList(): void
    {
        $input = (new MembershipInput())->roles();

        self::assertSame(['_links' => ['roles' => []]], $input->toArray());
    }

    public function testNotificationMessageProducesRawFormattable(): void
    {
        $input = (new MembershipInput())->notificationMessage('Granted permission to execute Order 66.');

        self::assertSame(
            ['_meta' => ['notificationMessage' => ['raw' => 'Granted permission to execute Order 66.']]],
            $input->toArray()
        );
    }

    public function testSendNotificationSetsMetaFlag(): void
    {
        $input = (new MembershipInput())->sendNotification(false);

        self::assertSame(['_meta' => ['sendNotification' => false]], $input->toArray());
    }

    public function testSetSetsArbitraryTopLevelProperty(): void
    {
        $input = (new MembershipInput())->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $input->toArray());
    }

    public function testChainingReturnsSameInstance(): void
    {
        $input = new MembershipInput();
        $result = $input->project(21);

        self::assertSame($input, $result);
    }

    public function testFullInputProducesExpectedPayload(): void
    {
        $input = (new MembershipInput())
            ->user(14)
            ->project(21)
            ->roles(4)
            ->notificationMessage('Granted permission to execute Order 66.')
            ->sendNotification(true);

        self::assertSame([
            '_links' => [
                'principal' => ['href' => '/api/v3/users/14'],
                'project' => ['href' => '/api/v3/projects/21'],
                'roles' => [
                    ['href' => '/api/v3/roles/4'],
                ],
            ],
            '_meta' => [
                'notificationMessage' => ['raw' => 'Granted permission to execute Order 66.'],
                'sendNotification' => true,
            ],
        ], $input->toArray());
    }

    public function testTypedRelationshipSetterWinsOverManualLinksOnCollision(): void
    {
        $input = (new MembershipInput())
            ->set('_links', [
                'customRel' => ['href' => '/api/v3/foo/1'],
                'project' => ['href' => '/api/v3/projects/1'],
            ])
            ->project(21);

        $payload = $input->toArray();

        self::assertIsArray($payload['_links']);
        /** @var array<string, array{href: string}> $links */
        $links = $payload['_links'];
        self::assertSame('/api/v3/foo/1', $links['customRel']['href']);
        self::assertSame('/api/v3/projects/21', $links['project']['href']);
    }

    public function testTypedMetaSetterWinsOverManualMetaOnCollision(): void
    {
        $input = (new MembershipInput())
            ->set('_meta', [
                'sendNotification' => false,
                'notificationMessage' => ['raw' => 'seeded'],
            ])
            ->notificationMessage('overridden');

        $payload = $input->toArray();

        self::assertIsArray($payload['_meta']);
        /** @var array<string, mixed> $meta */
        $meta = $payload['_meta'];
        self::assertSame(['raw' => 'overridden'], $meta['notificationMessage']);
        self::assertFalse($meta['sendNotification']);
    }

    public function testFromArrayRoundTrips(): void
    {
        $data = [
            '_links' => [
                'principal' => ['href' => '/api/v3/users/14'],
                'project' => ['href' => '/api/v3/projects/21'],
                'roles' => [
                    ['href' => '/api/v3/roles/4'],
                ],
            ],
            '_meta' => [
                'sendNotification' => false,
            ],
        ];

        self::assertSame($data, MembershipInput::fromArray($data)->toArray());
    }

    public function testFromArrayTypedLinkOverridesSeededLink(): void
    {
        $result = MembershipInput::fromArray([
            '_links' => ['project' => ['href' => '/api/v3/projects/1']],
        ])->project(21)->toArray();

        self::assertSame(
            ['_links' => ['project' => ['href' => '/api/v3/projects/21']]],
            $result
        );
    }

    public function testFromArrayTypedMetaOverridesSeededMeta(): void
    {
        $result = MembershipInput::fromArray([
            '_meta' => ['sendNotification' => true],
        ])->sendNotification(false)->toArray();

        self::assertSame(
            ['_meta' => ['sendNotification' => false]],
            $result
        );
    }
}
