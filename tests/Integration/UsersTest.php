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
        // There is no list method yet (T08b), so the seeded admin user at id 1
        // is used. A missing id skips rather than fails, so the test stays
        // valid against an instance where that user does not exist.
        try {
            $user = $this->client->users()->get(1);
        } catch (NotFoundException) {
            self::markTestSkipped('User 1 not found in the instance.');
        }

        self::assertSame(1, $user->id);
        self::assertNotSame('', $user->name);
    }
}
