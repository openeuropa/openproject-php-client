<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\AttachmentDigest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttachmentDigest::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class AttachmentDigestTest extends TestCase
{
    public function testDecodesAlgorithmAndHash(): void
    {
        $digest = AttachmentDigest::fromApiResponse([
            'algorithm' => 'md5',
            'hash' => '0a8d2241310c47be0c9893f042556808',
        ]);

        self::assertSame('md5', $digest->algorithm);
        self::assertSame('0a8d2241310c47be0c9893f042556808', $digest->hash);
    }

    public function testThrowsWhenRequiredAlgorithmMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        AttachmentDigest::fromApiResponse(['hash' => '0a8d2241310c47be0c9893f042556808']);
    }

    public function testThrowsWhenRequiredHashMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        AttachmentDigest::fromApiResponse(['algorithm' => 'md5']);
    }
}
