<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class UsersTest extends IntegrationTestCase
{
    public function testGetReturnsUserWithMatchingId(): void
    {
        try {
            $user = $this->client->users()->get(1);
        } catch (NotFoundException) {
            self::markTestSkipped('User 1 not found in the instance.');
        }

        self::assertSame(1, $user->id);
        self::assertNotSame('', $user->name);
    }

    public function testListPageYieldsUsersWithIds(): void
    {
        $results = $this->client->users()->listPage();

        if (empty($results->elements)) {
            self::markTestSkipped('No users found in the instance.');
        }

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

        if ($user === null) {
            self::markTestSkipped('No users found in the instance.');
        }

        self::assertGreaterThan(0, $user->id);
        self::assertNotSame('', $user->name);
    }
}
