<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Model\AttachmentDigest;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class AttachmentsTest extends IntegrationTestCase
{
    public function testUploadPersistsAttachment(): void
    {
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $attachment = $this->client->attachments()->upload(
            'phpclient integration test',
            $fileName,
            'text/plain',
            'A description of the file',
        );

        self::assertNotNull($attachment->id);
        self::assertSame($fileName, $attachment->fileName);
        self::assertNotEmpty($attachment->contentType);
        self::assertNotEmpty($attachment->status);
        self::assertInstanceOf(Formattable::class, $attachment->description);
        self::assertInstanceOf(AttachmentDigest::class, $attachment->digest);
        self::assertNotEmpty($attachment->digest->algorithm);
        self::assertNotEmpty($attachment->digest->hash);
    }
}
