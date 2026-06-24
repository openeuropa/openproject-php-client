<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class WorkPackagesTest extends IntegrationTestCase
{
    public function testGetReturnsWorkPackageWithMatchingId(): void
    {
        // A known seeded work package id is used. A missing id skips rather
        // than fails, so the test stays valid against an instance without that
        // work package.
        try {
            $workPackage = $this->client->workPackages()->get(2);
        } catch (NotFoundException) {
            self::markTestSkipped('Work package 2 not found in the instance.');
        }

        self::assertSame(2, $workPackage->id);
        self::assertNotEmpty($workPackage->subject);
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

        if (empty($projects->elements)) {
            self::markTestSkipped('No projects found in the instance.');
        }

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
        try {
            $workPackage = $this->client->workPackages()->get(2);
        } catch (NotFoundException) {
            self::markTestSkipped('Work package 2 not found in the instance.');
        }

        if ($workPackage->id === null) {
            self::markTestSkipped('Work package has no ID.');
        }

        $form = $this->client->workPackages()->updateForm($workPackage->id, [
            'subject' => 'phpclient updated subject',
            'lockVersion' => $workPackage->lockVersion,
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
        self::assertSame($subject, $created->subject);

        $fetched = $this->client->workPackages()->get($created->id);
        self::assertSame($subject, $fetched->subject);
    }
}
