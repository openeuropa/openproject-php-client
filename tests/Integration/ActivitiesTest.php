<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Model\Activity;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class ActivitiesTest extends IntegrationTestCase
{
    public function testCommentAndListActivitiesRoundTrip(): void
    {
        $workPackageId = $this->createWorkPackage()->id;
        self::assertNotNull($workPackageId);
        $raw = 'phpclient integration test comment ' . uniqid();

        $created = $this->client->activities()->comment($workPackageId, $raw);

        self::assertSame($raw, $created->comment?->raw);
        self::assertSame($workPackageId, $created->workPackage?->id());

        $activities = $this->client->activities()->allByWorkPackage($workPackageId);

        $comments = array_values(array_filter(
            $activities->elements,
            static fn (Activity $activity): bool => $activity->comment?->raw === $raw,
        ));
        self::assertCount(1, $comments, 'The posted comment must be present in the activities list.');
        self::assertSame($created->id, $comments[0]->id);
    }

    public function testCommentWithNotifyFalseDoesNotError(): void
    {
        $workPackageId = $this->createWorkPackage()->id;
        self::assertNotNull($workPackageId);

        $activity = $this->client->activities()->comment(
            $workPackageId,
            'phpclient integration test comment ' . uniqid(),
            false,
        );

        self::assertSame($workPackageId, $activity->workPackage?->id());
    }
}
