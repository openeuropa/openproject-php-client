<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\WorkPackageLinks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackageLinks::class)]
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

    public function testCustomField(): void
    {
        self::assertSame(
            ['customField26' => ['href' => '/api/v3/custom_options/5']],
            WorkPackageLinks::customField(26, 5, 'custom_options')
        );
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
