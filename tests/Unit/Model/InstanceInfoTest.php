<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\InstanceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstanceInfo::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class InstanceInfoTest extends TestCase
{
    public function testMapsInstanceNameAndCoreVersion(): void
    {
        $info = InstanceInfo::fromApiResponse([
            '_type' => 'Root',
            'instanceName' => 'My instance',
            'coreVersion' => '17.1.1',
        ]);

        self::assertSame('My instance', $info->instanceName);
        self::assertSame('17.1.1', $info->coreVersion);
    }

    public function testCoreVersionIsNullWhenAbsent(): void
    {
        // The spec marks coreVersion as admin-only: a non-admin token simply
        // does not receive the property.
        $info = InstanceInfo::fromApiResponse([
            '_type' => 'Root',
            'instanceName' => 'My instance',
        ]);

        self::assertNull($info->coreVersion);
    }

    public function testThrowsWhenInstanceNameIsMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('instanceName');

        InstanceInfo::fromApiResponse(['_type' => 'Root']);
    }
}
