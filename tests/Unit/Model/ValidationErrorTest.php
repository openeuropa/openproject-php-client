<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationError::class)]
#[UsesClass(HalResource::class)]
final class ValidationErrorTest extends TestCase
{
    public function testMapsAllFieldsFromAFullErrorObject(): void
    {
        $data = [
            '_type' => 'Error',
            'errorIdentifier' => 'urn:openproject-org:api:v3:errors:PropertyConstraintViolation',
            'message' => "Subject can't be blank.",
            '_embedded' => [
                'details' => [
                    'attribute' => 'subject',
                ],
            ],
        ];

        $error = ValidationError::fromApiResponse($data);

        self::assertSame('subject', $error->attribute);
        self::assertSame("Subject can't be blank.", $error->message);
        self::assertSame('urn:openproject-org:api:v3:errors:PropertyConstraintViolation', $error->identifier);
    }

    public function testAllFieldsAreNullWhenDataIsEmpty(): void
    {
        $error = ValidationError::fromApiResponse([]);

        self::assertNull($error->attribute);
        self::assertNull($error->message);
        self::assertNull($error->identifier);
    }
}
