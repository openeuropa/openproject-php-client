<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\ConflictException;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\WorkPackageInput;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversNothing]
final class WorkPackagesTest extends IntegrationTestCase
{
    public function testGetReturnsWorkPackageWithMatchingId(): void
    {
        $wp = $this->createWorkPackage();
        $id = $wp->id;

        $fetched = $this->client->workPackages()->get($id);
        self::assertSame($id, $fetched->id);
        self::assertNotEmpty($fetched->subject);
        // An unparseable timestamp reads as null, so these prove the live format parses.
        self::assertNotNull($fetched->createdAt);
        self::assertNotNull($fetched->updatedAt);
    }

    public function testListPageReturnsNonNegativeTotal(): void
    {
        $results = $this->client->workPackages()->listPage();

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    /**
     * @param list<string>|null $values
     */
    #[DataProvider('filterOperators')]
    public function testListPageAcceptsFilterOperator(string $name, FilterOperator $operator, ?array $values): void
    {
        $results = $this->client->workPackages()->listPage(new FilterSet(new Filter($name, $operator, $values)));

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    /**
     * @return array<string, array{string, FilterOperator, list<string>|null}>
     */
    public static function filterOperators(): array
    {
        return [
            'less than n days ago' => ['createdAt', FilterOperator::LESS_THAN_N_DAYS_AGO, ['5']],
            'more than n days ago' => ['createdAt', FilterOperator::MORE_THAN_N_DAYS_AGO, ['5']],
            'less than n days from now' => ['dueDate', FilterOperator::LESS_THAN_N_DAYS_FROM_NOW, ['5']],
            'more than n days from now' => ['dueDate', FilterOperator::MORE_THAN_N_DAYS_FROM_NOW, ['5']],
            'locked' => ['version', FilterOperator::LOCKED, null],
            'manually sorted' => ['manualSort', FilterOperator::MANUALLY_SORTED, []],
        ];
    }

    #[DataProvider('relationOperators')]
    public function testListPageAcceptsRelationOperatorOnRelatableFilter(FilterOperator $operator): void
    {
        $workPackage = $this->createWorkPackage();

        $results = $this->client->workPackages()->listPage(
            new FilterSet(new Filter('relatable', $operator, [(string) $workPackage->id])),
        );

        self::assertGreaterThanOrEqual(0, $results->total);
    }

    /**
     * @return array<string, array{FilterOperator}>
     */
    public static function relationOperators(): array
    {
        return [
            'relates' => [FilterOperator::RELATES],
            'precedes' => [FilterOperator::PRECEDES],
            'follows' => [FilterOperator::FOLLOWS],
            'blocks' => [FilterOperator::BLOCKS],
            'blocked' => [FilterOperator::BLOCKED],
            'duplicates' => [FilterOperator::DUPLICATES],
            'duplicated' => [FilterOperator::DUPLICATED],
            'includes' => [FilterOperator::INCLUDES],
            'part of' => [FilterOperator::PART_OF],
            'requires' => [FilterOperator::REQUIRES],
            'required' => [FilterOperator::REQUIRED],
            'parent' => [FilterOperator::PARENT],
            'child' => [FilterOperator::CHILD],
            'children' => [FilterOperator::CHILDREN],
        ];
    }

    public function testListPageElementsHaveIdAndSubject(): void
    {
        $results = $this->client->workPackages()->listPage();

        if (empty($results->elements)) {
            self::markTestSkipped('No work packages found in the instance.');
        }

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

        self::assertNotEmpty($first->subject);
    }

    public function testListByProjectYieldsWorkPackagesWithIdAndSubject(): void
    {
        $projectId = $this->firstActiveProjectId();

        $first = null;
        foreach ($this->client->workPackages()->listByProject($projectId) as $workPackage) {
            $first = $workPackage;
            break;
        }

        if ($first === null) {
            self::markTestSkipped('No work packages found in the project.');
        }

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

        $form = $this->client->workPackages()->updateForm(
            $id,
            (new WorkPackageInput())->subject('phpclient updated subject')->set('lockVersion', $wp->lockVersion),
        );

        self::assertInstanceOf(Form::class, $form);
        self::assertNotEmpty($form->schema);
    }

    public function testCreatePersistsWorkPackageAndCanBeReadBack(): void
    {
        $projectId = $this->firstActiveProjectId();

        $types = $this->client->types()->allByProject($projectId);
        self::assertNotEmpty($types->elements, 'The project must have at least one type.');
        $typeId = $types->elements[0]->id;

        $subject = 'phpclient-' . uniqid();
        $created = $this->client->workPackages()->create(
            (new WorkPackageInput())->subject($subject)->project($projectId)->type($typeId),
        );

        $this->createdWorkPackageIds[] = $created->id;
        self::assertSame($subject, $created->subject);

        $fetched = $this->client->workPackages()->get($created->id);
        self::assertSame($subject, $fetched->subject);
    }

    public function testDeleteRemovesWorkPackage(): void
    {
        $wp = $this->createWorkPackage();
        $id = $wp->id;

        $this->client->workPackages()->delete($id);

        $this->expectException(NotFoundException::class);
        $this->client->workPackages()->get($id);
    }

    public function testUpdateChangesSubjectAndCanBeReadBack(): void
    {
        $wp = $this->createWorkPackage();

        $newSubject = 'phpclient-updated-' . uniqid();
        self::assertNotNull($wp->lockVersion);

        $updated = $this->client->workPackages()->update(
            $wp->id,
            (new WorkPackageInput())->subject($newSubject),
            $wp->lockVersion,
        );

        self::assertSame($newSubject, $updated->subject);

        $fetched = $this->client->workPackages()->get($wp->id);
        self::assertSame($newSubject, $fetched->subject);
    }

    public function testUpdateWithStaleLockVersionThrowsConflictException(): void
    {
        $wp = $this->createWorkPackage();

        self::assertNotNull($wp->lockVersion);

        $originalLockVersion = $wp->lockVersion;

        // Advance the lock version with a successful update.
        $this->client->workPackages()->update(
            $wp->id,
            (new WorkPackageInput())->subject('phpclient-conflict-' . uniqid()),
            $originalLockVersion,
        );

        $this->expectException(ConflictException::class);
        $this->client->workPackages()->update(
            $wp->id,
            (new WorkPackageInput())->subject('phpclient-stale-' . uniqid()),
            $originalLockVersion,
        );
    }

    public function testUpdateWithInvalidPayloadThrowsValidationException(): void
    {
        $wp = $this->createWorkPackage();

        self::assertNotNull($wp->lockVersion);

        $this->expectException(ValidationException::class);
        $this->client->workPackages()->update($wp->id, (new WorkPackageInput())->subject(''), $wp->lockVersion);
    }
}
