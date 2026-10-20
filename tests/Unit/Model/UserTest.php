<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\CustomFields;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(CustomFields::class)]
#[UsesClass(Link::class)]
final class UserTest extends TestCase
{
    public function testDecodesUserFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/user.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $user = User::fromApiResponse($data);

        self::assertSame(1, $user->id);
        self::assertSame('John Sheppard', $user->name);
        self::assertSame('j.sheppard', $user->login);
        self::assertSame('John', $user->firstName);
        self::assertSame('Sheppard', $user->lastName);
        self::assertSame('shep@mail.com', $user->email);
        self::assertTrue($user->admin);
        self::assertSame('active', $user->status);
        self::assertSame('en', $user->language);
        self::assertSame('https://example.org/users/1/avatar', $user->avatar);
        self::assertEquals(new \DateTimeImmutable('2014-05-21T08:51:20.396Z'), $user->createdAt);
        self::assertEquals(new \DateTimeImmutable('2014-05-21T08:51:20.396Z'), $user->updatedAt);

        self::assertSame('User String CF value', $user->customFields->values['customField1']);
        self::assertCount(1, $user->customFields->links['customField2']);
        self::assertSame('/api/v3/custom_options/1', $user->customFields->links['customField2'][0]->href);
        self::assertSame('Option A', $user->customFields->links['customField2'][0]->title);
    }

    public function testPermissionGatedFieldsAreNullWhenAbsent(): void
    {
        // The spec requires only id and name; the rest are conditional on the
        // caller's permissions, so a minimal response leaves them null.
        $user = User::fromApiResponse(['id' => 5, 'name' => 'Jane Doe']);

        self::assertSame(5, $user->id);
        self::assertSame('Jane Doe', $user->name);
        self::assertNull($user->login);
        self::assertNull($user->firstName);
        self::assertNull($user->lastName);
        self::assertNull($user->email);
        self::assertNull($user->admin);
        self::assertNull($user->status);
        self::assertNull($user->language);
        self::assertNull($user->avatar);
        self::assertNull($user->createdAt);
        self::assertNull($user->updatedAt);
        self::assertSame([], $user->customFields->values);
        self::assertSame([], $user->customFields->links);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        User::fromApiResponse(['name' => 'Jane Doe']);
    }

    public function testThrowsWhenRequiredNameMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        User::fromApiResponse(['id' => 5]);
    }
}
