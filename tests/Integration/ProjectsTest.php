<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
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

        self::assertNotNull($results->elements[0]->name);
    }

    public function testAllGeneratorReturnsIterable(): void
    {
        $projects = iterator_to_array($this->client->projects()->all());

        self::assertNotEmpty($projects, 'The instance must have at least one project.');

        self::assertNotNull($projects[0]->name);
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

    public function testUpdateWithInvalidPayloadThrowsValidationException(): void
    {
        $created = $this->createProject();

        $this->expectException(ValidationException::class);
        $this->client->projects()->update($created->id, (new ProjectInput())->name(''));
    }

    public function testDeleteArchivesProjectForAsynchronousRemoval(): void
    {
        $created = $this->createProject();
        $id = $created->id;
        self::assertTrue($created->active);

        $this->client->projects()->delete($id);

        // Project deletion runs asynchronously: the project is archived at once
        // and removed later, so it is still readable here but no longer active.
        $archived = $this->client->projects()->get($id);
        self::assertFalse($archived->active);
    }
}
