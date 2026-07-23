<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\TimeEntry;
use OpenEuropa\OpenProjectClient\Model\TimeEntryInput;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class TimeEntriesTest extends IntegrationTestCase
{
    /**
     * IDs of time entries created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    private array $createdTimeEntryIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdTimeEntryIds as $id) {
            try {
                $this->client->timeEntries()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }

        parent::tearDown();
    }

    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->timeEntries()->get(999999);
    }

    public function testListPageDecodesTimeEntries(): void
    {
        $results = $this->client->timeEntries()->listPage();

        // The seeded instance may legitimately have no time entries, so the
        // count-matches-elements check holds regardless of how many exist.
        self::assertSame(count($results->elements), $results->count);

        foreach ($results->elements as $timeEntry) {
            self::assertInstanceOf(TimeEntry::class, $timeEntry);
            self::assertGreaterThan(0, $timeEntry->id);
        }
    }

    public function testAllGeneratorYieldsEveryTimeEntry(): void
    {
        $total = $this->client->timeEntries()->listPage()->total;

        $yielded = 0;
        foreach ($this->client->timeEntries()->all() as $timeEntry) {
            self::assertGreaterThan(0, $timeEntry->id);
            $yielded++;
        }

        self::assertSame($total, $yielded);
    }

    public function testCreateFormReturnsFormWithSchema(): void
    {
        $form = $this->client->timeEntries()->createForm();
        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testCreatePersistsTimeEntryAndCanBeReadBack(): void
    {
        $comment = 'phpclient time entry ' . uniqid();
        $created = $this->createTimeEntry($comment);

        self::assertSame($comment, $created->comment?->raw);

        $fetched = $this->client->timeEntries()->get($created->id);
        self::assertSame($comment, $fetched->comment?->raw);

        $this->client->timeEntries()->delete($created->id);

        $this->expectException(NotFoundException::class);
        $this->client->timeEntries()->get($created->id);
    }

    public function testUpdateFormReturnsFormWithSchema(): void
    {
        $created = $this->createTimeEntry('phpclient time entry ' . uniqid());

        $form = $this->client->timeEntries()->updateForm(
            $created->id,
            (new TimeEntryInput())->comment('phpclient updated ' . uniqid()),
        );

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testUpdateChangesCommentAndCanBeReadBack(): void
    {
        $created = $this->createTimeEntry('phpclient time entry ' . uniqid());

        $newComment = 'phpclient-updated-' . uniqid();
        $updated = $this->client->timeEntries()->update(
            $created->id,
            (new TimeEntryInput())->comment($newComment),
        );

        self::assertSame($newComment, $updated->comment?->raw);

        $fetched = $this->client->timeEntries()->get($created->id);
        self::assertSame($newComment, $fetched->comment?->raw);
    }

    public function testUpdateWithInvalidPayloadThrowsValidationException(): void
    {
        $created = $this->createTimeEntry('phpclient time entry ' . uniqid());

        $this->expectException(ValidationException::class);
        $this->client->timeEntries()->update($created->id, (new TimeEntryInput())->hours('not-a-duration'));
    }

    private function createTimeEntry(string $comment): TimeEntry
    {
        $workPackage = $this->createWorkPackage();

        $timeEntry = $this->client->timeEntries()->create(
            (new TimeEntryInput())
                ->spentOn(date('Y-m-d'))
                ->hours('PT2H')
                ->comment($comment)
                ->project($this->firstActiveProjectId())
                ->workPackage($workPackage->id),
        );

        $this->createdTimeEntryIds[] = $timeEntry->id;

        return $timeEntry;
    }
}
