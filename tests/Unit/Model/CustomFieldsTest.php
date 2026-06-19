<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Model\CustomFields;
use OpenEuropa\OpenProjectClient\Model\Link;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CustomFields::class)]
#[UsesClass(Link::class)]
final class CustomFieldsTest extends TestCase
{
    public function testEmptyDataProducesEmptyBag(): void
    {
        $bag = CustomFields::fromApiResponse([]);

        self::assertSame([], $bag->values);
        self::assertSame([], $bag->links);
    }

    public function testTopLevelCustomFieldsOfVariedTypesLandInValues(): void
    {
        $data = [
            'customField1' => 'a string',
            'customField2' => 99,
            'customField3' => true,
            'customField4' => null,
            'customField5' => ['format' => 'markdown', 'raw' => '**bold**', 'html' => '<b>bold</b>'],
            'id' => 42,
            'name' => 'ordinary key',
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertSame('a string', $bag->values['customField1']);
        self::assertSame(99, $bag->values['customField2']);
        self::assertTrue($bag->values['customField3']);
        self::assertNull($bag->values['customField4']);
        self::assertSame(
            ['format' => 'markdown', 'raw' => '**bold**', 'html' => '<b>bold</b>'],
            $bag->values['customField5'],
        );
        self::assertArrayNotHasKey('id', $bag->values);
        self::assertArrayNotHasKey('name', $bag->values);
        self::assertSame([], $bag->links);
    }

    public function testKeyWithoutDigitSuffixIsIgnored(): void
    {
        $data = [
            'customFields' => 'plural, no digit suffix',
            'customActions' => 'another non-matching key',
            'customField1' => 'this one matches',
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertArrayNotHasKey('customFields', $bag->values);
        self::assertArrayNotHasKey('customActions', $bag->values);
        self::assertSame('this one matches', $bag->values['customField1']);
    }

    public function testSingleReferenceLinkBecomesOneElementList(): void
    {
        $data = [
            '_links' => [
                'customField10' => [
                    'href' => '/api/v3/users/7',
                    'title' => 'Jane Doe',
                ],
            ],
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertArrayHasKey('customField10', $bag->links);
        self::assertCount(1, $bag->links['customField10']);
        self::assertSame('/api/v3/users/7', $bag->links['customField10'][0]->href);
        self::assertSame('Jane Doe', $bag->links['customField10'][0]->title);
        self::assertSame([], $bag->values);
    }

    public function testMultiValueLinkArrayBecomesMultiElementList(): void
    {
        $data = [
            '_links' => [
                'customField20' => [
                    ['href' => '/api/v3/custom_options/1', 'title' => 'Option A'],
                    ['href' => '/api/v3/custom_options/2', 'title' => 'Option B'],
                ],
            ],
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertArrayHasKey('customField20', $bag->links);
        self::assertCount(2, $bag->links['customField20']);
        self::assertSame('/api/v3/custom_options/1', $bag->links['customField20'][0]->href);
        self::assertSame('Option B', $bag->links['customField20'][1]->title);
    }

    public function testNonArrayElementInMultiValueListIsSkipped(): void
    {
        $data = [
            '_links' => [
                'customField30' => [
                    ['href' => '/api/v3/custom_options/1', 'title' => 'Option A'],
                    'not-an-array',
                    ['href' => '/api/v3/custom_options/3', 'title' => 'Option C'],
                ],
            ],
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertArrayHasKey('customField30', $bag->links);
        self::assertCount(2, $bag->links['customField30']);
        self::assertSame('/api/v3/custom_options/1', $bag->links['customField30'][0]->href);
        self::assertSame('/api/v3/custom_options/3', $bag->links['customField30'][1]->href);
    }

    public function testLinksAbsentProducesEmptyLinksMap(): void
    {
        $bag = CustomFields::fromApiResponse(['customField1' => 'value']);

        self::assertSame(['customField1' => 'value'], $bag->values);
        self::assertSame([], $bag->links);
    }

    public function testLinksPresentButNoCustomFieldEntriesProducesEmptyLinksMap(): void
    {
        $data = [
            '_links' => [
                'self' => ['href' => '/api/v3/work_packages/2'],
                'customFields' => ['href' => '/projects/demo/settings/custom_fields', 'title' => 'Custom fields'],
                'status' => ['href' => '/api/v3/statuses/1', 'title' => 'New'],
            ],
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertSame([], $bag->values);
        self::assertSame([], $bag->links);
    }

    public function testNonArrayLinkEntryIsIgnored(): void
    {
        $data = [
            '_links' => [
                'customField5' => 'not-an-array',
                'customField6' => ['href' => '/api/v3/users/1', 'title' => 'Alice'],
            ],
        ];

        $bag = CustomFields::fromApiResponse($data);

        self::assertArrayNotHasKey('customField5', $bag->links);
        self::assertArrayHasKey('customField6', $bag->links);
        self::assertCount(1, $bag->links['customField6']);
    }
}
