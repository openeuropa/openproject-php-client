<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
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

        self::assertNotNull($created->id);
        self::assertSame($name, $created->name);

        $fetched = $this->client->projects()->get($created->id);
        self::assertSame($name, $fetched->name);
    }

    public function testListPageCountsCreatedProject(): void
    {
        $suffix = uniqid();
        $name = 'phpclient project ' . $suffix;
        $identifier = 'phpclient-' . $suffix;

        $this->client->projects()->create(
            (new ProjectInput())->name($name)->identifier($identifier),
        );

        $results = $this->client->projects()->listPage();

        self::assertGreaterThanOrEqual(1, $results->total);
    }

    public function testListPageElementsHaveIdAndName(): void
    {
        $results = $this->client->projects()->listPage();

        self::assertNotEmpty($results->elements, 'The instance must have at least one project.');

        self::assertNotNull($results->elements[0]->id);
        self::assertNotNull($results->elements[0]->name);
    }

    public function testAllGeneratorReturnsIterable(): void
    {
        $projects = iterator_to_array($this->client->projects()->all());

        self::assertNotEmpty($projects, 'The instance must have at least one project.');

        self::assertNotNull($projects[0]->id);
        self::assertNotNull($projects[0]->name);
    }

    public function testGetReturnsProjectWithMatchingId(): void
    {
        $results = $this->client->projects()->listPage();

        self::assertNotEmpty($results->elements, 'The instance must have at least one project.');

        $firstId = $results->elements[0]->id;
        self::assertNotNull($firstId);

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
        $suffix = uniqid();
        $name = 'phpclient project ' . $suffix;
        $identifier = 'phpclient-' . $suffix;

        $created = $this->client->projects()->create(
            (new ProjectInput())->name($name)->identifier($identifier),
        );

        self::assertNotNull($created->id);

        $form = $this->client->projects()->updateForm(
            $created->id,
            (new ProjectInput())->name('phpclient updated ' . $suffix),
        );

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateChangesNameAndCanBeReadBack(): void
    {
        $suffix = uniqid();
        $name = 'phpclient-' . $suffix;
        $identifier = 'phpclient-' . $suffix;

        $created = $this->client->projects()->create(
            (new ProjectInput())->name($name)->identifier($identifier),
        );

        self::assertNotNull($created->id);

        $newName = 'phpclient-updated-' . $suffix;
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
        $suffix = uniqid();
        $name = 'phpclient-' . $suffix;
        $identifier = 'phpclient-' . $suffix;

        $created = $this->client->projects()->create(
            (new ProjectInput())->name($name)->identifier($identifier),
        );

        self::assertNotNull($created->id);

        $this->expectException(ValidationException::class);
        $this->client->projects()->update($created->id, (new ProjectInput())->name(''));
    }
}
