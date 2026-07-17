<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\Relation;
use OpenEuropa\OpenProjectClient\Model\RelationInput;
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
            (new RelationInput())->type('relates')->to($to->id),
        );

        self::assertSame('relates', $relation->type);
        self::assertNotNull($relation->from);
        self::assertNotNull($relation->to);
        self::assertSame($from->id, $relation->from->id());
        self::assertSame($to->id, $relation->to->id());

        $fetched = $this->client->relations()->get($relation->id);
        self::assertSame($relation->id, $fetched->id);
        self::assertSame('relates', $fetched->type);

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
            (new RelationInput())->type('relates')->to($to->id),
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
            (new RelationInput())->type('relates')->to($to->id),
        );

        $this->expectException(ValidationException::class);
        $this->client->relations()->update($relation->id, (new RelationInput())->type('notarelationtype'));
    }
}
