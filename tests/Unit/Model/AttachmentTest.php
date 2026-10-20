<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Attachment;
use OpenEuropa\OpenProjectClient\Model\AttachmentDigest;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attachment::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(AttachmentDigest::class)]
final class AttachmentTest extends TestCase
{
    /**
     * A complete valid base array with every required field present.
     *
     * @return array<string, mixed>
     */
    private static function baseData(): array
    {
        return [
            'id' => 3,
            'fileName' => 'phpclient-test.txt',
        ];
    }

    public function testDecodesAttachmentFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/attachment.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $attachment = Attachment::fromApiResponse($data);

        self::assertSame(3, $attachment->id);
        self::assertSame('phpclient-test.txt', $attachment->fileName);
        self::assertSame(35, $attachment->fileSize);
        self::assertSame('uploaded', $attachment->status);
        self::assertSame('text/plain', $attachment->contentType);
        self::assertEquals(new \DateTimeImmutable('2026-07-01T17:02:16.648Z'), $attachment->createdAt);

        self::assertSame('plain', $attachment->description?->format);

        self::assertNotNull($attachment->digest);
        self::assertSame('md5', $attachment->digest->algorithm);
        self::assertSame('0a8d2241310c47be0c9893f042556808', $attachment->digest->hash);

        self::assertSame(3, $attachment->self?->id());
        self::assertSame(1, $attachment->container?->id());
        self::assertSame(4, $attachment->author?->id());
        self::assertSame('/api/v3/attachments/3/content', $attachment->downloadLocation?->href);
    }

    public function testAbsentOptionalFieldsAreNull(): void
    {
        $attachment = Attachment::fromApiResponse(self::baseData());

        self::assertNull($attachment->fileSize);
        self::assertNull($attachment->description);
        self::assertNull($attachment->status);
        self::assertNull($attachment->contentType);
        self::assertNull($attachment->digest);
        self::assertNull($attachment->createdAt);
        self::assertNull($attachment->self);
        self::assertNull($attachment->container);
        self::assertNull($attachment->author);
        self::assertNull($attachment->downloadLocation);
    }

    public function testMalformedDescriptionAndDigestAreNull(): void
    {
        $attachment = Attachment::fromApiResponse(self::baseData() + [
            'description' => ['raw' => 'A description without a format'],
            'digest' => ['algorithm' => 'md5'],
        ]);

        self::assertNull($attachment->description);
        self::assertNull($attachment->digest);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $data = self::baseData();
        unset($data['id']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredFileNameMissing(): void
    {
        $data = self::baseData();
        unset($data['fileName']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }
}
