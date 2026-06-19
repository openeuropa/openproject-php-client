<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Exception;

use OpenEuropa\OpenProjectClient\Exception\ApiException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationException::class)]
#[UsesClass(ApiException::class)]
#[UsesClass(ValidationError::class)]
final class ValidationExceptionTest extends TestCase
{
    public function testConstructorExposesErrorsAndParentFields(): void
    {
        $urn = 'urn:openproject-org:api:v3:errors:PropertyConstraintViolation';
        $error = new ValidationError('subject', "Subject can't be blank.", $urn);
        $exception = new ValidationException(
            'Multiple field constraints have been violated.',
            422,
            'urn:openproject-org:api:v3:errors:MultipleErrors',
            [$error],
        );

        self::assertSame(422, $exception->httpStatus);
        self::assertSame('urn:openproject-org:api:v3:errors:MultipleErrors', $exception->errorIdentifier);
        self::assertCount(1, $exception->errors);
        self::assertSame('subject', $exception->errors[0]->attribute);
    }
}
