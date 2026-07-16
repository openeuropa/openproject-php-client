<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model\Form;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Form::class)]
#[UsesClass(SchemaProperty::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(Link::class)]
#[UsesClass(HalResource::class)]
final class FormTest extends TestCase
{
    public function testDecodesFixtureWithAllEmbeddedSections(): void
    {
        $path = __DIR__ . '/../../../fixtures/form.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $form = Form::fromApiResponse($data);

        self::assertSame('An example title', $form->payload['subject']);
        self::assertSame(5, $form->payload['lockVersion']);

        self::assertArrayHasKey('lockVersion', $form->schema);
        self::assertArrayHasKey('subject', $form->schema);
        self::assertArrayHasKey('status', $form->schema);
        self::assertCount(3, $form->schema);
        self::assertArrayNotHasKey('_type', $form->schema);
        self::assertArrayNotHasKey('_links', $form->schema);
        self::assertArrayNotHasKey('_dependencies', $form->schema);

        $statusProp = $form->schema['status'];
        self::assertCount(2, $statusProp->allowedValues);
        self::assertSame('_links', $statusProp->location);

        self::assertTrue($form->hasErrors());
        self::assertArrayHasKey('subject', $form->validationErrors);
        self::assertSame("Subject can't be blank.", $form->validationErrors['subject']->message);
        self::assertSame('subject', $form->validationErrors['subject']->attribute);
    }

    public function testDecodesFixtureWithSingleLinkAllowedValues(): void
    {
        $path = __DIR__ . '/../../../fixtures/work-package-form.json';
        $data = json_decode((string) file_get_contents($path), true);

        /** @var array<string, mixed> $data */
        $form = Form::fromApiResponse($data);

        $assigneeProp = $form->schema['assignee'];
        self::assertSame([], $assigneeProp->allowedValues);
        self::assertNotNull($assigneeProp->allowedValuesLink);
        self::assertSame('/api/v3/workspaces/1/available_assignees', $assigneeProp->allowedValuesLink->href);

        $typeProp = $form->schema['type'];
        self::assertCount(3, $typeProp->allowedValues);
        self::assertNull($typeProp->allowedValuesLink);
    }

    public function testEmptyDataProducesEmptyForm(): void
    {
        $form = Form::fromApiResponse([]);

        self::assertSame([], $form->payload);
        self::assertSame([], $form->schema);
        self::assertSame([], $form->validationErrors);
        self::assertFalse($form->hasErrors());
    }

    public function testEmptyValidationErrorsProducesNoErrors(): void
    {
        $data = [
            '_embedded' => [
                'validationErrors' => [],
            ],
        ];

        $form = Form::fromApiResponse($data);

        self::assertFalse($form->hasErrors());
        self::assertSame([], $form->validationErrors);
    }

    public function testSchemaSkipsNonArrayValues(): void
    {
        $data = [
            '_embedded' => [
                'schema' => [
                    'subject' => [
                        'type' => 'String',
                        'name' => 'Subject',
                        'required' => true,
                        'hasDefault' => false,
                        'writable' => true,
                    ],
                    'badEntry' => 'not-an-array',
                ],
            ],
        ];

        $form = Form::fromApiResponse($data);

        self::assertArrayHasKey('subject', $form->schema);
        self::assertArrayNotHasKey('badEntry', $form->schema);
        self::assertCount(1, $form->schema);
    }

    public function testSchemaSkipsMetaKeys(): void
    {
        $data = [
            '_embedded' => [
                'schema' => [
                    '_type' => 'Schema',
                    '_links' => ['self' => ['href' => '/api/v3/example/schema']],
                    '_dependencies' => [],
                    'subject' => [
                        'type' => 'String',
                        'name' => 'Subject',
                        'required' => true,
                        'hasDefault' => false,
                        'writable' => true,
                    ],
                ],
            ],
        ];

        $form = Form::fromApiResponse($data);

        self::assertArrayNotHasKey('_type', $form->schema);
        self::assertArrayNotHasKey('_links', $form->schema);
        self::assertArrayNotHasKey('_dependencies', $form->schema);
        self::assertArrayHasKey('subject', $form->schema);
    }

    public function testValidationErrorsSkipsNonArrayValues(): void
    {
        $data = [
            '_embedded' => [
                'validationErrors' => [
                    'subject' => [
                        '_type' => 'Error',
                        'errorIdentifier' => 'urn:openproject-org:api:v3:errors:PropertyConstraintViolation',
                        'message' => "Subject can't be blank.",
                        '_embedded' => ['details' => ['attribute' => 'subject']],
                    ],
                    'badEntry' => 'not-an-array',
                ],
            ],
        ];

        $form = Form::fromApiResponse($data);

        self::assertArrayHasKey('subject', $form->validationErrors);
        self::assertArrayNotHasKey('badEntry', $form->validationErrors);
        self::assertCount(1, $form->validationErrors);
        self::assertTrue($form->hasErrors());
    }

    public function testNonArrayPayloadProducesEmptyPayload(): void
    {
        $data = [
            '_embedded' => [
                'payload' => 'not-an-array',
            ],
        ];

        $form = Form::fromApiResponse($data);

        self::assertSame([], $form->payload);
    }
}
