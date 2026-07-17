<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Version::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
final class VersionTest extends TestCase
{
    /**
     * A complete valid base array with every required field present.
     *
     * @return array<string, mixed>
     */
    private static function baseData(): array
    {
        return [
            'id' => 3,
            'name' => 'v0.9.1-alpha',
            'description' => [
                'format' => 'plain',
                'raw' => 'First draft of the energy weapon',
                'html' => '<p>First draft of the energy weapon</p>',
            ],
            'status' => 'closed',
            'sharing' => 'none',
            'createdAt' => '2026-02-06T09:30:49.157Z',
            'updatedAt' => '2026-02-10T09:53:21.620Z',
        ];
    }

    public function testDecodesVersionFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/version.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $version = Version::fromApiResponse($data);

        self::assertSame(3, $version->id);
        self::assertSame('v0.9.1-alpha', $version->name);
        self::assertSame('closed', $version->status);
        self::assertSame('none', $version->sharing);
        self::assertSame('2026-02-06T09:30:49.157Z', $version->createdAt);
        self::assertSame('2026-02-10T09:53:21.620Z', $version->updatedAt);
        self::assertSame('plain', $version->description->format);

        self::assertSame(3, $version->self?->id());
        self::assertSame(10, $version->definingProject?->id());
        self::assertSame('/api/v3/versions/3/workspaces', $version->availableInProjects?->href);
    }

    public function testAbsentStartAndEndDateAreNull(): void
    {
        $version = Version::fromApiResponse(self::baseData());

        self::assertNull($version->startDate);
        self::assertNull($version->endDate);
        self::assertNull($version->self);
        self::assertNull($version->definingProject);
        self::assertNull($version->availableInProjects);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $data = self::baseData();
        unset($data['id']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredNameMissing(): void
    {
        $data = self::baseData();
        unset($data['name']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredDescriptionMissing(): void
    {
        $data = self::baseData();
        unset($data['description']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredStatusMissing(): void
    {
        $data = self::baseData();
        unset($data['status']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredSharingMissing(): void
    {
        $data = self::baseData();
        unset($data['sharing']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredCreatedAtMissing(): void
    {
        $data = self::baseData();
        unset($data['createdAt']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredUpdatedAtMissing(): void
    {
        $data = self::baseData();
        unset($data['updatedAt']);

        $this->expectException(UnexpectedResponseException::class);
        Version::fromApiResponse($data);
    }
}
