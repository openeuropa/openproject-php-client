<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Category;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Category::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(Link::class)]
final class CategoryTest extends TestCase
{
    public function testDecodesCategoryFixture(): void
    {
        $path = __DIR__ . '/../../fixtures/category.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $category = Category::fromApiResponse($data);

        self::assertSame(10, $category->id);
        self::assertSame('Foo', $category->name);
        self::assertSame(10, $category->self?->id());
        self::assertSame(11, $category->project?->id());
        self::assertSame(42, $category->defaultAssignee?->id());
    }

    public function testAbsentOptionalFieldsAreNull(): void
    {
        $category = Category::fromApiResponse([
            '_type' => 'Category',
            'id' => 11,
            'name' => 'Frontend',
        ]);

        self::assertSame(11, $category->id);
        self::assertSame('Frontend', $category->name);
        self::assertNull($category->self);
        self::assertNull($category->project);
        self::assertNull($category->defaultAssignee);
    }

    public function testThrowsWhenRequiredIdMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        Category::fromApiResponse(['_type' => 'Category', 'name' => 'Frontend']);
    }

    public function testThrowsWhenRequiredNameMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        Category::fromApiResponse(['_type' => 'Category', 'id' => 11]);
    }
}
