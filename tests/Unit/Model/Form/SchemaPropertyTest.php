<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model\Form;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaProperty::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Link::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class SchemaPropertyTest extends TestCase
{
    /**
     * A complete valid base array with all required fields present.
     *
     * @return array<string, mixed>
     */
    private static function baseData(): array
    {
        return [
            'type' => 'String',
            'name' => 'Subject',
            'required' => true,
            'hasDefault' => false,
            'writable' => true,
        ];
    }

    public function testMapsAllFieldsIncludingAllowedValues(): void
    {
        $data = [
            'type' => 'Status',
            'name' => 'Status',
            'required' => true,
            'hasDefault' => true,
            'writable' => true,
            'location' => '_links',
            'placeholder' => 'Pick a status',
            '_links' => [
                'allowedValues' => [
                    ['href' => '/api/v3/statuses/1', 'title' => 'New'],
                    ['href' => '/api/v3/statuses/2', 'title' => 'Closed'],
                ],
            ],
        ];

        $prop = SchemaProperty::fromApiResponse($data);

        self::assertSame('Status', $prop->type);
        self::assertSame('Status', $prop->name);
        self::assertTrue($prop->required);
        self::assertTrue($prop->hasDefault);
        self::assertTrue($prop->writable);
        self::assertSame('_links', $prop->location);
        self::assertSame('Pick a status', $prop->placeholder);
        self::assertCount(2, $prop->allowedValues);
        self::assertSame('/api/v3/statuses/1', $prop->allowedValues[0]->href);
        self::assertSame('New', $prop->allowedValues[0]->title);
        self::assertSame('/api/v3/statuses/2', $prop->allowedValues[1]->href);
        self::assertSame('Closed', $prop->allowedValues[1]->title);
        self::assertNull($prop->allowedValuesLink);
    }

    public function testAllowedValuesAsSingleLinkPopulatesAllowedValuesLink(): void
    {
        $data = self::baseData();
        $data['_links'] = [
            'allowedValues' => ['href' => '/api/v3/workspaces/1/available_assignees'],
        ];

        $prop = SchemaProperty::fromApiResponse($data);

        self::assertSame([], $prop->allowedValues);
        self::assertInstanceOf(Link::class, $prop->allowedValuesLink);
        self::assertSame('/api/v3/workspaces/1/available_assignees', $prop->allowedValuesLink->href);
    }

    public function testMinimalPropertyWithOnlyRequiredFields(): void
    {
        $prop = SchemaProperty::fromApiResponse(self::baseData());

        self::assertSame('String', $prop->type);
        self::assertSame('Subject', $prop->name);
        self::assertTrue($prop->required);
        self::assertFalse($prop->hasDefault);
        self::assertTrue($prop->writable);
        self::assertNull($prop->location);
        self::assertNull($prop->placeholder);
        self::assertSame([], $prop->allowedValues);
        self::assertNull($prop->allowedValuesLink);
    }

    public function testAllowedValuesSkipsNonArrayElements(): void
    {
        $data = self::baseData();
        $data['_links'] = [
            'allowedValues' => [
                ['href' => '/api/v3/statuses/1', 'title' => 'New'],
                'not-an-array',
                ['href' => '/api/v3/statuses/3', 'title' => 'In Progress'],
            ],
        ];

        $prop = SchemaProperty::fromApiResponse($data);

        self::assertCount(2, $prop->allowedValues);
        self::assertSame('/api/v3/statuses/1', $prop->allowedValues[0]->href);
        self::assertSame('/api/v3/statuses/3', $prop->allowedValues[1]->href);
    }

    public function testLinksWithNoAllowedValuesKeyProducesEmptyList(): void
    {
        $data = self::baseData();
        $data['_links'] = ['self' => ['href' => '/api/v3/example']];

        $prop = SchemaProperty::fromApiResponse($data);

        self::assertSame([], $prop->allowedValues);
    }

    public function testAbsentLinksProducesEmptyAllowedValues(): void
    {
        $prop = SchemaProperty::fromApiResponse(self::baseData());

        self::assertSame([], $prop->allowedValues);
    }

    public function testThrowsWhenRequiredTypeMissing(): void
    {
        $data = self::baseData();
        unset($data['type']);

        $this->expectException(UnexpectedResponseException::class);
        SchemaProperty::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredNameMissing(): void
    {
        $data = self::baseData();
        unset($data['name']);

        $this->expectException(UnexpectedResponseException::class);
        SchemaProperty::fromApiResponse($data);
    }

    public function testThrowsWhenRequiredRequiredMissing(): void
    {
        $data = self::baseData();
        unset($data['required']);

        $this->expectException(UnexpectedResponseException::class);
        SchemaProperty::fromApiResponse($data);
    }

    public function testHasDefaultIsNullWhenAbsent(): void
    {
        // The spec marks hasDefault required, but OpenProject omits it on some
        // properties (for example "subject"), so it is read leniently.
        $data = self::baseData();
        unset($data['hasDefault']);

        $prop = SchemaProperty::fromApiResponse($data);

        self::assertNull($prop->hasDefault);
    }

    public function testThrowsWhenRequiredWritableMissing(): void
    {
        $data = self::baseData();
        unset($data['writable']);

        $this->expectException(UnexpectedResponseException::class);
        SchemaProperty::fromApiResponse($data);
    }
}
