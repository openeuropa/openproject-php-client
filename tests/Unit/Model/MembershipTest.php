<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\Membership;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Membership::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Link::class)]
final class MembershipTest extends TestCase
{
    /**
     * A complete valid base array with every required field present.
     *
     * @return array<string, mixed>
     */
    private static function baseData(): array
    {
        return [
            'id' => 11,
        ];
    }

    public function testDecodesMembershipFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/membership.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $membership = Membership::fromApiResponse($data);

        self::assertSame(11, $membership->id);
        self::assertSame('2015-03-20T12:56:56.626Z', $membership->createdAt);
        self::assertSame('2018-12-20T18:16:11.563Z', $membership->updatedAt);
        self::assertSame(11, $membership->self?->id());
        self::assertSame(3, $membership->project?->id());
        self::assertSame(5, $membership->principal?->id());
        self::assertCount(1, $membership->roles);
        self::assertSame(4, $membership->roles[0]->id());
        self::assertSame('Sith Lord', $membership->roles[0]->title);
    }

    public function testAbsentOptionalFieldsAreNullOrEmpty(): void
    {
        $membership = Membership::fromApiResponse(self::baseData());

        self::assertNull($membership->createdAt);
        self::assertNull($membership->updatedAt);
        self::assertNull($membership->self);
        self::assertNull($membership->project);
        self::assertNull($membership->principal);
        self::assertSame([], $membership->roles);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $data = self::baseData();
        unset($data['id']);

        $this->expectException(UnexpectedResponseException::class);
        Membership::fromApiResponse($data);
    }
}
