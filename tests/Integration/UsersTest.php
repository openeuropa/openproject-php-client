<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class UsersTest extends IntegrationTestCase
{
    public function testGetReturnsUserWithMatchingId(): void
    {
        $user = $this->client->users()->get(1);

        self::assertSame(1, $user->id);
        self::assertNotSame('', $user->name);
    }

    public function testListPageYieldsUsersWithIds(): void
    {
        $results = $this->client->users()->listPage();

        self::assertNotEmpty($results->elements, 'The instance must have at least one user.');

        $user = $results->elements[0];

        self::assertGreaterThan(0, $user->id);
        self::assertNotSame('', $user->name);
    }

    public function testAllGeneratorReturnsIterable(): void
    {
        $user = null;

        foreach ($this->client->users()->all() as $item) {
            $user = $item;
            break;
        }

        self::assertNotNull($user, 'The instance must have at least one user.');

        self::assertGreaterThan(0, $user->id);
        self::assertNotSame('', $user->name);
    }
}
