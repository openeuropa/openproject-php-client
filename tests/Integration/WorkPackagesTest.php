<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\ConflictException;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class WorkPackagesTest extends IntegrationTestCase
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

    public function testGetReturnsWorkPackageWithMatchingId(): void
    {
        $wp = $this->createWorkPackage();
        $id = $wp->id;
        self::assertNotNull($id);

        $fetched = $this->client->workPackages()->get($id);
        self::assertSame($id, $fetched->id);
        self::assertNotEmpty($fetched->subject);
    }

    public function testListPageReturnsNonNegativeTotal(): void
    {
        $results = $this->client->workPackages()->listPage();

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    public function testListPageElementsHaveIdAndSubject(): void
    {
        $results = $this->client->workPackages()->listPage();

        if (empty($results->elements)) {
            self::markTestSkipped('No work packages found in the instance.');
        }

        self::assertNotNull($results->elements[0]->id);
        self::assertNotEmpty($results->elements[0]->subject);
    }

    public function testAllYieldsWorkPackagesWithIdAndSubject(): void
    {
        $first = null;
        foreach ($this->client->workPackages()->all() as $workPackage) {
            $first = $workPackage;
            break;
        }

        if ($first === null) {
            self::markTestSkipped('No work packages found in the instance.');
        }

        self::assertNotNull($first->id);
        self::assertNotEmpty($first->subject);
    }

    public function testListByProjectYieldsWorkPackagesWithIdAndSubject(): void
    {
        $projects = $this->client->projects()->listPage();

        self::assertNotEmpty($projects->elements, 'The instance must have at least one project.');

        $projectId = $projects->elements[0]->id;
        self::assertNotNull($projectId);

        $first = null;
        foreach ($this->client->workPackages()->listByProject($projectId) as $workPackage) {
            $first = $workPackage;
            break;
        }

        if ($first === null) {
            self::markTestSkipped('No work packages found in the project.');
        }

        self::assertNotNull($first->id);
        self::assertNotEmpty($first->subject);
    }

    public function testCreateFormReturnsFormWithSchema(): void
    {
        $form = $this->client->workPackages()->createForm();

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateFormReturnsFormWithSchema(): void
    {
        $wp = $this->createWorkPackage();
        $id = $wp->id;
        self::assertNotNull($id);

        $form = $this->client->workPackages()->updateForm($id, [
            'subject' => 'phpclient updated subject',
            'lockVersion' => $wp->lockVersion,
        ]);

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testCreatePersistsWorkPackageAndCanBeReadBack(): void
    {
        $projects = $this->client->projects()->listPage();
        self::assertNotEmpty($projects->elements, 'The instance must have at least one project.');
        $projectId = $projects->elements[0]->id;
        self::assertNotNull($projectId);

        $types = $this->client->types()->allByProject($projectId);
        self::assertNotEmpty($types->elements, 'The project must have at least one type.');
        $typeId = $types->elements[0]->id;

        $subject = 'phpclient-' . uniqid();
        $created = $this->client->workPackages()->create([
            'subject' => $subject,
            '_links' => [
                'project' => ['href' => '/api/v3/projects/' . $projectId],
                'type' => ['href' => '/api/v3/types/' . $typeId],
            ],
        ]);

        self::assertNotNull($created->id);
        $this->createdWorkPackageIds[] = $created->id;
        self::assertSame($subject, $created->subject);

        $fetched = $this->client->workPackages()->get($created->id);
        self::assertSame($subject, $fetched->subject);
    }

    public function testDeleteRemovesWorkPackage(): void
    {
        $wp = $this->createWorkPackage();
        $id = $wp->id;
        self::assertNotNull($id);

        $this->client->workPackages()->delete($id);

        $this->expectException(NotFoundException::class);
        $this->client->workPackages()->get($id);
    }

    public function testUpdateChangesSubjectAndCanBeReadBack(): void
    {
        $wp = $this->createWorkPackage();

        $newSubject = 'phpclient-updated-' . uniqid();
        self::assertNotNull($wp->id);
        self::assertNotNull($wp->lockVersion);

        $updated = $this->client->workPackages()->update($wp->id, ['subject' => $newSubject], $wp->lockVersion);

        self::assertSame($newSubject, $updated->subject);

        $fetched = $this->client->workPackages()->get($wp->id);
        self::assertSame($newSubject, $fetched->subject);
    }

    public function testUpdateWithStaleLockVersionThrowsConflictException(): void
    {
        $wp = $this->createWorkPackage();

        self::assertNotNull($wp->id);
        self::assertNotNull($wp->lockVersion);

        $originalLockVersion = $wp->lockVersion;

        // Advance the lock version with a successful update.
        $this->client->workPackages()->update(
            $wp->id,
            ['subject' => 'phpclient-conflict-' . uniqid()],
            $originalLockVersion,
        );

        $this->expectException(ConflictException::class);
        $this->client->workPackages()->update(
            $wp->id,
            ['subject' => 'phpclient-stale-' . uniqid()],
            $originalLockVersion,
        );
    }

    public function testUpdateWithInvalidPayloadThrowsValidationException(): void
    {
        $wp = $this->createWorkPackage();

        self::assertNotNull($wp->id);
        self::assertNotNull($wp->lockVersion);

        $this->expectException(ValidationException::class);
        $this->client->workPackages()->update($wp->id, ['subject' => ''], $wp->lockVersion);
    }

    private function createWorkPackage(): WorkPackage
    {
        $projects = $this->client->projects()->listPage();
        self::assertNotEmpty($projects->elements, 'The instance must have at least one project.');
        $projectId = $projects->elements[0]->id;
        self::assertNotNull($projectId);

        $types = $this->client->types()->allByProject($projectId);
        self::assertNotEmpty($types->elements, 'The project must have at least one type.');
        $typeId = $types->elements[0]->id;

        $wp = $this->client->workPackages()->create([
            'subject' => 'phpclient-' . uniqid(),
            '_links' => [
                'project' => ['href' => '/api/v3/projects/' . $projectId],
                'type' => ['href' => '/api/v3/types/' . $typeId],
            ],
        ]);

        $id = $wp->id;
        self::assertNotNull($id);
        $this->createdWorkPackageIds[] = $id;

        return $wp;
    }
}
