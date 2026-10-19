<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\AttachmentDigest;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class AttachmentsTest extends IntegrationTestCase
{
    /**
     * IDs of containerless attachments created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    private array $createdAttachmentIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdAttachmentIds as $id) {
            try {
                $this->client->attachments()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }

        parent::tearDown();
    }

    public function testUploadPersistsAttachment(): void
    {
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $attachment = $this->client->attachments()->upload(
            (new HttpFactory())->createStream('phpclient integration test'),
            $fileName,
            'text/plain',
            'A description of the file',
        );

        $this->createdAttachmentIds[] = $attachment->id;
        self::assertSame($fileName, $attachment->fileName);
        self::assertNotEmpty($attachment->contentType);
        self::assertNotEmpty($attachment->status);
        self::assertInstanceOf(Formattable::class, $attachment->description);
        self::assertInstanceOf(AttachmentDigest::class, $attachment->digest);
        self::assertNotEmpty($attachment->digest->algorithm);
        self::assertNotEmpty($attachment->digest->hash);
    }

    public function testUploadToWorkPackagePersistsAttachmentOnContainer(): void
    {
        $workPackageId = $this->createWorkPackage()->id;
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $attachment = $this->client->attachments()->uploadToWorkPackage(
            $workPackageId,
            (new HttpFactory())->createStream('phpclient integration test'),
            $fileName,
            'text/plain',
            'A description of the file',
        );

        self::assertSame($fileName, $attachment->fileName);
        self::assertNotEmpty($attachment->contentType);
        self::assertNotEmpty($attachment->status);
        self::assertInstanceOf(Formattable::class, $attachment->description);
        self::assertInstanceOf(AttachmentDigest::class, $attachment->digest);
        self::assertNotEmpty($attachment->digest->algorithm);
        self::assertNotEmpty($attachment->digest->hash);
        self::assertNotNull($attachment->container);
        self::assertSame($workPackageId, $attachment->container->id());
    }

    public function testGetReturnsUploadedAttachment(): void
    {
        $workPackageId = $this->createWorkPackage()->id;
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $uploaded = $this->client->attachments()->uploadToWorkPackage(
            $workPackageId,
            (new HttpFactory())->createStream('phpclient integration test'),
            $fileName,
            'text/plain',
            'A description of the file',
        );

        $attachment = $this->client->attachments()->get($uploaded->id);

        self::assertSame($uploaded->id, $attachment->id);
        self::assertSame($uploaded->fileName, $attachment->fileName);
        self::assertSame($uploaded->contentType, $attachment->contentType);
        self::assertSame($uploaded->digest->algorithm, $attachment->digest->algorithm);
        self::assertSame($uploaded->digest->hash, $attachment->digest->hash);
        self::assertNotNull($attachment->container);
        self::assertSame($workPackageId, $attachment->container->id());
    }

    public function testDownloadReturnsUploadedContent(): void
    {
        $contents = 'phpclient download test ' . uniqid();
        $workPackageId = $this->createWorkPackage()->id;
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $uploaded = $this->client->attachments()->uploadToWorkPackage(
            $workPackageId,
            (new HttpFactory())->createStream($contents),
            $fileName,
            'text/plain',
        );

        $stream = $this->client->attachments()->download($uploaded->id);

        self::assertSame($contents, (string) $stream);
    }

    public function testDeleteRemovesAttachment(): void
    {
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $attachment = $this->client->attachments()->upload(
            (new HttpFactory())->createStream('phpclient integration test'),
            $fileName,
            'text/plain',
        );
        $id = $attachment->id;
        $this->createdAttachmentIds[] = $id;

        $this->client->attachments()->delete($id);

        $this->expectException(NotFoundException::class);
        $this->client->attachments()->get($id);
    }
}
