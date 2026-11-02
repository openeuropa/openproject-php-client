<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Relation;
use OpenEuropa\OpenProjectClient\Model\RelationInput;
use OpenEuropa\OpenProjectClient\Model\RelationType;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class RelationsTest extends IntegrationTestCase
{
    public function testAllDecodesEveryRelationWithAnIntId(): void
    {
        $results = $this->client->relations()->all();

        self::assertNotEmpty($results->elements);

        foreach ($results->elements as $relation) {
            self::assertInstanceOf(Relation::class, $relation);
            self::assertGreaterThan(0, $relation->id);
        }
    }

    public function testListPageReturnsOnePageOfTheRequestedSize(): void
    {
        $workPackage = $this->createWorkPackage();
        $relationIds = [];
        foreach ([$this->createWorkPackage(), $this->createWorkPackage()] as $other) {
            $relationIds[] = $this->client->relations()->create(
                $workPackage->id,
                (new RelationInput())->type(RelationType::RELATES)->to($other->id),
            )->id;
        }

        $filters = new FilterSet(new Filter('involved', FilterOperator::EQUAL, [(string) $workPackage->id]));
        $first = $this->client->relations()->listPage($filters, new CollectionParams(pageSize: 1));
        $second = $this->client->relations()->listPage($filters, new CollectionParams(offset: 2, pageSize: 1));

        self::assertSame(2, $first->total);
        self::assertSame(1, $first->count);
        self::assertSame(1, $first->pageSize);
        self::assertNotNull($first->nextByOffset);
        self::assertSame(2, $second->offset);
        self::assertNull($second->nextByOffset);

        $listedIds = [$first->elements[0]->id, $second->elements[0]->id];
        sort($listedIds);
        sort($relationIds);
        self::assertSame($relationIds, $listedIds);
    }

    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->relations()->get(999999);
    }

    public function testCreateRelateWorkPackagesThenReadAndDelete(): void
    {
        $from = $this->createWorkPackage();
        $to = $this->createWorkPackage();

        $relation = $this->client->relations()->create(
            $from->id,
            (new RelationInput())->type(RelationType::RELATES)->to($to->id),
        );

        self::assertSame(RelationType::RELATES, $relation->type);
        self::assertNotNull($relation->from);
        self::assertNotNull($relation->to);
        self::assertSame($from->id, $relation->from->id());
        self::assertSame($to->id, $relation->to->id());

        $fetched = $this->client->relations()->get($relation->id);
        self::assertSame($relation->id, $fetched->id);
        self::assertSame(RelationType::RELATES, $fetched->type);
        self::assertSame(RelationType::RELATES, $fetched->reverseType);

        $this->client->relations()->delete($relation->id);

        $this->expectException(NotFoundException::class);
        $this->client->relations()->get($relation->id);
    }

    public function testUpdateChangesDescriptionAndCanBeReadBack(): void
    {
        $from = $this->createWorkPackage();
        $to = $this->createWorkPackage();

        $relation = $this->client->relations()->create(
            $from->id,
            (new RelationInput())->type(RelationType::RELATES)->to($to->id),
        );

        $newDescription = 'phpclient-updated-' . uniqid();
        $updated = $this->client->relations()->update(
            $relation->id,
            (new RelationInput())->description($newDescription),
        );

        self::assertSame($newDescription, $updated->description);

        $fetched = $this->client->relations()->get($relation->id);
        self::assertSame($newDescription, $fetched->description);
    }

    public function testUpdateWithInvalidTypeThrowsValidationException(): void
    {
        $from = $this->createWorkPackage();
        $to = $this->createWorkPackage();

        $relation = $this->client->relations()->create(
            $from->id,
            (new RelationInput())->type(RelationType::RELATES)->to($to->id),
        );

        $this->expectException(ValidationException::class);
        $this->client->relations()->update($relation->id, (new RelationInput())->set('type', 'notarelationtype'));
    }
}
