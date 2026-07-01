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
            'fileName' => 'phpclient-test.txt',
            'description' => [
                'format' => 'plain',
                'raw' => 'A user provided description of the file',
                'html' => '<p>A user provided description of the file</p>',
            ],
            'status' => 'uploaded',
            'contentType' => 'text/plain',
            'digest' => [
                'algorithm' => 'md5',
                'hash' => '0a8d2241310c47be0c9893f042556808',
            ],
            'createdAt' => '2026-07-01T17:02:16.648Z',
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
        self::assertSame('2026-07-01T17:02:16.648Z', $attachment->createdAt);

        self::assertSame('plain', $attachment->description->format);

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

        self::assertNull($attachment->id);
        self::assertNull($attachment->fileSize);
        self::assertNull($attachment->self);
        self::assertNull($attachment->container);
        self::assertNull($attachment->author);
        self::assertNull($attachment->downloadLocation);
    }

    public function testThrowsWhenRequiredFileNameMissing(): void
    {
        $data = self::baseData();
        unset($data['fileName']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredDescriptionMissing(): void
    {
        $data = self::baseData();
        unset($data['description']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredStatusMissing(): void
    {
        $data = self::baseData();
        unset($data['status']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredContentTypeMissing(): void
    {
        $data = self::baseData();
        unset($data['contentType']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredDigestMissing(): void
    {
        $data = self::baseData();
        unset($data['digest']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredCreatedAtMissing(): void
    {
        $data = self::baseData();
        unset($data['createdAt']);

        $this->expectException(UnexpectedResponseException::class);
        Attachment::fromApiResponse($data);
    }
}
