<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\CustomFieldLinkResource;
use OpenEuropa\OpenProjectClient\Model\WorkPackageLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackageLinks::class)]
#[UsesClass(CustomFieldLinkResource::class)]
final class WorkPackageLinksTest extends TestCase
{
    public function testType(): void
    {
        self::assertSame(['type' => ['href' => '/api/v3/types/3']], WorkPackageLinks::type(3));
    }

    public function testStatus(): void
    {
        self::assertSame(['status' => ['href' => '/api/v3/statuses/2']], WorkPackageLinks::status(2));
    }

    public function testPriority(): void
    {
        self::assertSame(['priority' => ['href' => '/api/v3/priorities/1']], WorkPackageLinks::priority(1));
    }

    public function testProject(): void
    {
        self::assertSame(['project' => ['href' => '/api/v3/projects/5']], WorkPackageLinks::project(5));
    }

    public function testAssignee(): void
    {
        self::assertSame(['assignee' => ['href' => '/api/v3/users/7']], WorkPackageLinks::assignee(7));
    }

    public function testResponsible(): void
    {
        self::assertSame(['responsible' => ['href' => '/api/v3/users/8']], WorkPackageLinks::responsible(8));
    }

    public function testParent(): void
    {
        self::assertSame(['parent' => ['href' => '/api/v3/work_packages/10']], WorkPackageLinks::parent(10));
    }

    public function testVersion(): void
    {
        self::assertSame(['version' => ['href' => '/api/v3/versions/4']], WorkPackageLinks::version(4));
    }

    public function testCategory(): void
    {
        self::assertSame(['category' => ['href' => '/api/v3/categories/6']], WorkPackageLinks::category(6));
    }

    #[DataProvider('customFieldLinkResources')]
    public function testCustomField(CustomFieldLinkResource $resource, string $href): void
    {
        self::assertSame(['customField26' => ['href' => $href]], WorkPackageLinks::customField(26, 5, $resource));
    }

    /**
     * @return array<string, array{CustomFieldLinkResource, string}>
     */
    public static function customFieldLinkResources(): array
    {
        return [
            'list option' => [CustomFieldLinkResource::CUSTOM_OPTIONS, '/api/v3/custom_options/5'],
            'user' => [CustomFieldLinkResource::USERS, '/api/v3/users/5'],
            'group' => [CustomFieldLinkResource::GROUPS, '/api/v3/groups/5'],
            'placeholder user' => [CustomFieldLinkResource::PLACEHOLDER_USERS, '/api/v3/placeholder_users/5'],
            'version' => [CustomFieldLinkResource::VERSIONS, '/api/v3/versions/5'],
            'hierarchy item' => [CustomFieldLinkResource::CUSTOM_FIELD_ITEMS, '/api/v3/custom_field_items/5'],
        ];
    }

    public function testFragmentsCompose(): void
    {
        $merged = WorkPackageLinks::type(1) + WorkPackageLinks::status(2);

        self::assertSame([
            'type' => ['href' => '/api/v3/types/1'],
            'status' => ['href' => '/api/v3/statuses/2'],
        ], $merged);
    }
}
