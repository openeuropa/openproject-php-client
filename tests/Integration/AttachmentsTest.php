<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\AttachmentDigest;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\WorkPackageInput;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class AttachmentsTest extends IntegrationTestCase
{
    /**
     * IDs of work packages created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    private array $createdWorkPackageIds = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->createdWorkPackageIds as $id) {
            try {
                $this->client->workPackages()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }
    }

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

    public function testUploadToWorkPackagePersistsAttachmentOnContainer(): void
    {
        $workPackageId = $this->createWorkPackage();
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $attachment = $this->client->attachments()->uploadToWorkPackage(
            $workPackageId,
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
        self::assertNotNull($attachment->container);
        self::assertSame($workPackageId, $attachment->container->id());
    }

    public function testGetReturnsUploadedAttachment(): void
    {
        $workPackageId = $this->createWorkPackage();
        $fileName = 'phpclient-' . uniqid() . '.txt';

        $uploaded = $this->client->attachments()->uploadToWorkPackage(
            $workPackageId,
            'phpclient integration test',
            $fileName,
            'text/plain',
            'A description of the file',
        );
        self::assertNotNull($uploaded->id);

        $attachment = $this->client->attachments()->get($uploaded->id);

        self::assertSame($uploaded->id, $attachment->id);
        self::assertSame($uploaded->fileName, $attachment->fileName);
        self::assertSame($uploaded->contentType, $attachment->contentType);
        self::assertSame($uploaded->digest->algorithm, $attachment->digest->algorithm);
        self::assertSame($uploaded->digest->hash, $attachment->digest->hash);
        self::assertNotNull($attachment->container);
        self::assertSame($workPackageId, $attachment->container->id());
    }

    /**
     * Creates a work package and returns its ID, registering it for teardown.
     */
    private function createWorkPackage(): int
    {
        $projects = $this->client->projects()->listPage(
            new FilterSet(new Filter('active', FilterOperator::EQUAL, ['t'])),
        );
        self::assertNotEmpty($projects->elements, 'The instance must have at least one active project.');
        $projectId = $projects->elements[0]->id;
        self::assertNotNull($projectId);

        $types = $this->client->types()->allByProject($projectId);
        self::assertNotEmpty($types->elements, 'The project must have at least one type.');

        $workPackage = $this->client->workPackages()->create(
            (new WorkPackageInput())->subject('phpclient-' . uniqid())
                ->project($projectId)
                ->type($types->elements[0]->id),
        );
        $workPackageId = $workPackage->id;
        self::assertNotNull($workPackageId);
        $this->createdWorkPackageIds[] = $workPackageId;

        return $workPackageId;
    }
}
