<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageResults::class)]
#[UsesClass(HalResource::class)]
final class PageResultsTest extends TestCase
{
    public function testDecodesCollectionFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/statuses.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $results = PageResults::fromApiResponse($data, static fn (array $e): array => $e);

        self::assertSame(6, $results->total);
        self::assertSame(6, $results->count);
        self::assertCount(6, $results->elements);
        self::assertSame(1, $results->elements[0]['id']);
    }

    public function testIterationCoversGetIterator(): void
    {
        $path = __DIR__ . '/../../fixtures/statuses.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $results = PageResults::fromApiResponse($data, static fn (array $e): array => $e);

        $collected = iterator_to_array($results);

        self::assertCount(6, $collected);
        self::assertSame(1, $collected[0]['id']);
    }

    public function testMixedEmbeddedElementsSkipsNonArrayEntries(): void
    {
        $data = [
            '_embedded' => [
                'elements' => [
                    'not-an-array',
                    ['id' => 99],
                ],
            ],
        ];

        $results = PageResults::fromApiResponse($data, static fn (array $e): array => $e);

        self::assertSame(0, $results->total);
        self::assertSame(0, $results->count);
        self::assertCount(1, $results->elements);
        self::assertSame(99, $results->elements[0]['id']);
    }

    public function testEmptyArrayProducesZeroCountsAndNoElements(): void
    {
        $results = PageResults::fromApiResponse([], static fn (array $e): array => $e);

        self::assertSame(0, $results->total);
        self::assertSame(0, $results->count);
        self::assertCount(0, $results->elements);
    }
}
