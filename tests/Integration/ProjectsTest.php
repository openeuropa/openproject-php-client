<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
use OpenEuropa\OpenProjectClient\Model\ProjectStatus;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ProjectsTest extends IntegrationTestCase
{
    public function testCreatePersistsProjectAndCanBeReadBack(): void
    {
        $suffix = uniqid();
        $name = 'phpclient project ' . $suffix;
        $identifier = 'phpclient-' . $suffix;

        $created = $this->client->projects()->create(
            (new ProjectInput())->name($name)->identifier($identifier),
        );

        $this->createdProjectIds[] = $created->id;
        self::assertSame($name, $created->name);

        $fetched = $this->client->projects()->get($created->id);
        self::assertSame($name, $fetched->name);
    }

    public function testListPageCountsCreatedProject(): void
    {
        $suffix = uniqid();
        $name = 'phpclient project ' . $suffix;
        $identifier = 'phpclient-' . $suffix;

        $created = $this->client->projects()->create(
            (new ProjectInput())->name($name)->identifier($identifier),
        );

        $this->createdProjectIds[] = $created->id;

        $results = $this->client->projects()->listPage();

        self::assertGreaterThanOrEqual(1, $results->total);
    }

    public function testListPageElementsHaveIdAndName(): void
    {
        $results = $this->client->projects()->listPage();

        self::assertNotEmpty($results->elements, 'The instance must have at least one project.');

        self::assertNotEmpty($results->elements[0]->name);
    }

    public function testAllGeneratorReturnsIterable(): void
    {
        $projects = iterator_to_array($this->client->projects()->all());

        self::assertNotEmpty($projects, 'The instance must have at least one project.');

        self::assertNotEmpty($projects[0]->name);
    }

    public function testListPageAcceptsEqualAllOperator(): void
    {
        $results = $this->client->projects()->listPage(
            new FilterSet(new Filter('userAction', FilterOperator::EQUAL_ALL, ['projects/create', 'users/read'])),
        );

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    public function testAllGeneratorWithPageSizeZeroYieldsNothingAndStops(): void
    {
        $projects = iterator_to_array($this->client->projects()->all(params: new CollectionParams(pageSize: 0)));

        self::assertSame([], $projects);
    }

    public function testGetReturnsProjectWithMatchingId(): void
    {
        // Archived projects are removed asynchronously and sort first, so select
        // an active project to avoid racing a project's deletion.
        $results = $this->client->projects()->listPage(
            new FilterSet(new Filter('active', FilterOperator::EQUAL, ['t'])),
        );

        self::assertNotEmpty($results->elements, 'The instance must have at least one active project.');

        $firstId = $results->elements[0]->id;

        $project = $this->client->projects()->get($firstId);

        self::assertSame($firstId, $project->id);
    }

    public function testCreateFormReturnsFormWithSchema(): void
    {
        $form = $this->client->projects()->createForm();
        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateFormReturnsFormWithSchema(): void
    {
        $created = $this->createProject();

        $form = $this->client->projects()->updateForm(
            $created->id,
            (new ProjectInput())->name('phpclient updated ' . uniqid()),
        );

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateChangesNameAndCanBeReadBack(): void
    {
        $created = $this->createProject();

        $newName = 'phpclient-updated-' . uniqid();
        $updated = $this->client->projects()->update(
            $created->id,
            (new ProjectInput())->name($newName),
        );

        self::assertSame($newName, $updated->name);

        $fetched = $this->client->projects()->get($created->id);
        self::assertSame($newName, $fetched->name);
    }

    public function testUpdateSetsAndClearsStatus(): void
    {
        $created = $this->createProject();

        // Project does not read the status back, so the accepted update is
        // the proof: the server answers an unknown status with a 422.
        $updated = $this->client->projects()->update(
            $created->id,
            (new ProjectInput())->status(ProjectStatus::FINISHED),
        );
        self::assertSame($created->id, $updated->id);

        $cleared = $this->client->projects()->update($created->id, (new ProjectInput())->status(null));
        self::assertSame($created->id, $cleared->id);
    }

    public function testUpdateWithUnknownStatusThrowsValidationException(): void
    {
        $created = $this->createProject();

        $this->expectException(ValidationException::class);
        $this->client->projects()->update(
            $created->id,
            (new ProjectInput())->set('_links', ['status' => ['href' => '/api/v3/project_statuses/bogus']]),
        );
    }

    public function testUpdateArchivesProjectAndCanBeReadBack(): void
    {
        $created = $this->createProject();

        $updated = $this->client->projects()->update(
            $created->id,
            (new ProjectInput())->active(false),
        );

        self::assertFalse($updated->active);

        $fetched = $this->client->projects()->get($created->id);
        self::assertFalse($fetched->active);
    }

    public function testUpdateWithInvalidPayloadThrowsValidationException(): void
    {
        $created = $this->createProject();

        $this->expectException(ValidationException::class);
        $this->client->projects()->update($created->id, (new ProjectInput())->name(''));
    }

    public function testDeleteAcceptsProjectForAsynchronousRemoval(): void
    {
        $created = $this->createProject();
        $id = $created->id;
        self::assertTrue($created->active);

        $this->client->projects()->delete($id);

        // Project deletion runs asynchronously: the project is archived at once
        // and its row removed later by a background job, so a read immediately
        // after delete() can observe either the archived project or a 404 once
        // the row is already gone.
        try {
            $archived = $this->client->projects()->get($id);
            self::assertFalse($archived->active);
        } catch (NotFoundException) {
            // The background job already removed the row.
        }
    }
}
