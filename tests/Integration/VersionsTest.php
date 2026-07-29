<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Version;
use OpenEuropa\OpenProjectClient\Model\VersionInput;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class VersionsTest extends IntegrationTestCase
{
    /**
     * IDs of versions created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    private array $createdVersionIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdVersionIds as $id) {
            try {
                $this->client->versions()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }

        parent::tearDown();
    }

    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->versions()->get(999999);
    }

    public function testAllDecodesEveryVersionWithAnIntId(): void
    {
        $results = $this->client->versions()->all();

        // The seed's demo project is archived shortly after boot, which drops
        // its versions from this collection, so the collection may be empty.
        self::assertSame(count($results->elements), $results->count);

        foreach ($results->elements as $version) {
            self::assertInstanceOf(Version::class, $version);
            self::assertGreaterThan(0, $version->id);
        }
    }

    public function testAllByProjectDecodesVersionsForTheDefiningProject(): void
    {
        $results = $this->client->versions()->all();
        if ($results->elements === []) {
            self::markTestSkipped('The instance has no versions to derive a project id from.');
        }

        $projectId = $results->elements[0]->definingProject?->id();
        if ($projectId === null) {
            self::markTestSkipped('The first version has no resolvable defining project id.');
        }

        $projectResults = $this->client->versions()->allByProject($projectId);

        foreach ($projectResults->elements as $version) {
            self::assertInstanceOf(Version::class, $version);
        }
    }

    public function testCreatePersistsVersionAndCanBeReadBack(): void
    {
        $project = $this->createProject();

        $name = 'phpclient version ' . uniqid();
        $created = $this->createVersion($project->id, $name);

        self::assertSame($name, $created->name);

        $fetched = $this->client->versions()->get($created->id);
        self::assertSame($name, $fetched->name);

        $this->client->versions()->delete($created->id);

        $this->expectException(NotFoundException::class);
        $this->client->versions()->get($created->id);
    }

    public function testCreateFormReturnsFormWithSchema(): void
    {
        $form = $this->client->versions()->createForm();
        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateFormReturnsFormWithSchema(): void
    {
        $project = $this->createProject();
        $created = $this->createVersion($project->id, 'phpclient version ' . uniqid());

        $form = $this->client->versions()->updateForm(
            $created->id,
            (new VersionInput())->name('phpclient updated ' . uniqid()),
        );

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateChangesNameAndCanBeReadBack(): void
    {
        $project = $this->createProject();
        $created = $this->createVersion($project->id, 'phpclient version ' . uniqid());

        $newName = 'phpclient-updated-' . uniqid();
        $updated = $this->client->versions()->update(
            $created->id,
            (new VersionInput())->name($newName),
        );

        self::assertSame($newName, $updated->name);

        $fetched = $this->client->versions()->get($created->id);
        self::assertSame($newName, $fetched->name);
    }

    public function testUpdateWithInvalidPayloadThrowsValidationException(): void
    {
        $project = $this->createProject();
        $created = $this->createVersion($project->id, 'phpclient version ' . uniqid());

        $this->expectException(ValidationException::class);
        $this->client->versions()->update($created->id, (new VersionInput())->status('not-a-status'));
    }

    private function createVersion(int $projectId, string $name): Version
    {
        $version = $this->client->versions()->create(
            (new VersionInput())->name($name)->status('open')->sharing('none')->definingProject($projectId),
        );

        $this->createdVersionIds[] = $version->id;

        return $version;
    }
}
