<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\Membership;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MembershipsTest extends IntegrationTestCase
{
    public function testGetWithNonexistentIdThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->client->memberships()->get(999999);
    }

    public function testListPageDecodesMemberships(): void
    {
        $results = $this->client->memberships()->listPage();

        // The seeded instance may legitimately have no memberships visible
        // to the requesting token, so the count-matches-elements check holds
        // regardless of how many exist.
        self::assertSame(count($results->elements), $results->count);

        foreach ($results->elements as $membership) {
            self::assertInstanceOf(Membership::class, $membership);
            self::assertGreaterThan(0, $membership->id);
        }
    }

    public function testAllGeneratorYieldsEveryMembership(): void
    {
        $total = $this->client->memberships()->listPage()->total;

        $yielded = 0;
        foreach ($this->client->memberships()->all() as $membership) {
            self::assertGreaterThan(0, $membership->id);
            $yielded++;
        }

        self::assertSame($total, $yielded);
    }

    public function testGetReturnsMembershipFromTheFirstListed(): void
    {
        $results = $this->client->memberships()->listPage();
        if ($results->elements === []) {
            self::markTestSkipped('The instance has no memberships visible to the requesting token.');
        }

        $first = $results->elements[0];
        $fetched = $this->client->memberships()->get($first->id);

        self::assertSame($first->id, $fetched->id);
    }
}
