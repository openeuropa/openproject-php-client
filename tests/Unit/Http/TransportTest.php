<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Http;

use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Exception\ApiErrorException;
use OpenEuropa\OpenProjectClient\Exception\ApiException;
use OpenEuropa\OpenProjectClient\Exception\ApiUnreachableException;
use OpenEuropa\OpenProjectClient\Exception\AuthenticationException;
use OpenEuropa\OpenProjectClient\Exception\ConflictException;
use OpenEuropa\OpenProjectClient\Exception\ForbiddenException;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(ApiException::class)]
#[UsesClass(AuthenticationException::class)]
#[UsesClass(ForbiddenException::class)]
#[UsesClass(NotFoundException::class)]
#[UsesClass(ApiErrorException::class)]
#[UsesClass(ApiUnreachableException::class)]
#[UsesClass(ConflictException::class)]
#[UsesClass(ValidationException::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(HalResource::class)]
final class TransportTest extends TestCase
{
    use MockClientTrait;

    public function testSendsAnAuthenticatedHalGetToTheApiRoot(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}');

        $transport->get();

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://example.test/api/v3/', (string) $request->getUri());
        self::assertSame('application/hal+json', $request->getHeaderLine('Accept'));
        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $request->getHeaderLine('Authorization'),
        );
    }

    public function testResolvesResourcePathsAgainstTheApiRoot(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}');

        $transport->get('work_packages/42');

        self::assertSame(
            'http://example.test/api/v3/work_packages/42',
            (string) $this->lastRequest()->getUri(),
        );
    }

    public function testSupportsSubpathHostedInstances(): void
    {
        $transport = $this->createTransport('http://example.test/openproject/');
        $this->queueJsonResponse('{}');

        $transport->get();

        self::assertSame(
            'http://example.test/openproject/api/v3/',
            (string) $this->lastRequest()->getUri(),
        );
    }

    public function testAppendsQueryParametersEncodedPerRfc3986(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}');

        $transport->get('projects', ['filters' => '[{"name":{"operator":"~","values":["a b"]}}]', 'offset' => '2']);

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringStartsWith('http://example.test/api/v3/projects?', $uri);
        self::assertStringContainsString('offset=2', $uri);
        // RFC 3986 encodes a space as %20, never as the "+" of the default mode.
        self::assertStringContainsString('a%20b', $uri);
        self::assertStringNotContainsString('+', $uri);
    }

    public function testDecodesTheJsonBody(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('root.json');

        self::assertSame('OpenProject', $transport->get()['instanceName']);
    }

    public function testGetRawReturnsRawResponseBodyStreamWithoutDecoding(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('raw file bytes', 200);

        $stream = $transport->getRaw('attachments/3/content');

        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame('raw file bytes', (string) $stream);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://example.test/api/v3/attachments/3/content', (string) $request->getUri());
        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $request->getHeaderLine('Authorization'),
        );
        self::assertSame('', $request->getHeaderLine('Accept'));
    }

    public function testThrowsWhenTheResponseBodyIsNotValidJson(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('<html>not json</html>');

        $this->expectException(OpenProjectException::class);
        $this->expectExceptionMessage('not valid JSON');

        $transport->get();
    }

    public function testThrowsWhenTheJsonBodyIsNotAnObject(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('42');

        $this->expectException(OpenProjectException::class);
        $this->expectExceptionMessage('non-object JSON body');

        $transport->get();
    }

    public function testMapsUnauthenticatedResponseToAuthenticationException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-401-unauthenticated.json', 401);

        try {
            $transport->get();
            self::fail('Expected an AuthenticationException.');
        } catch (AuthenticationException $exception) {
            self::assertSame(401, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:Unauthenticated',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsForbiddenResponseToForbiddenException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-403-missing-permission.json', 403);

        try {
            $transport->get();
            self::fail('Expected a ForbiddenException.');
        } catch (ForbiddenException $exception) {
            self::assertSame(403, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:MissingPermission',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsNotFoundResponseToNotFoundException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-404-not-found.json', 404);

        try {
            $transport->get();
            self::fail('Expected a NotFoundException.');
        } catch (NotFoundException $exception) {
            self::assertSame(404, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:NotFound',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsOtherClientErrorToApiErrorException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-400-invalid-query.json', 400);

        try {
            $transport->get();
            self::fail('Expected an ApiErrorException.');
        } catch (ApiErrorException $exception) {
            self::assertSame(400, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:InvalidQuery',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsServerErrorToApiUnreachableException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-500-internal.json', 500);

        try {
            $transport->get();
            self::fail('Expected an ApiUnreachableException.');
        } catch (ApiUnreachableException $exception) {
            self::assertSame(500, $exception->httpStatus);
        }
    }

    public function testMapsNetworkFailureToApiUnreachableException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueConnectionFailure();

        try {
            $transport->get();
            self::fail('Expected an ApiUnreachableException.');
        } catch (ApiUnreachableException $exception) {
            self::assertNull($exception->httpStatus);
            self::assertInstanceOf(ClientExceptionInterface::class, $exception->getPrevious());
        }
    }

    public function testMapsAnErrorWithANonJsonBodyAndLeavesTheIdentifierNull(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('<html>Gateway error</html>', 404);

        try {
            $transport->get();
            self::fail('Expected a NotFoundException.');
        } catch (NotFoundException $exception) {
            self::assertSame(404, $exception->httpStatus);
            self::assertNull($exception->errorIdentifier);
        }
    }

    public function testMapsAnErrorWithANonObjectJsonBodyAndLeavesTheIdentifierNull(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('"just a string"', 403);

        try {
            $transport->get();
            self::fail('Expected a ForbiddenException.');
        } catch (ForbiddenException $exception) {
            self::assertSame(403, $exception->httpStatus);
            self::assertNull($exception->errorIdentifier);
        }
    }

    public function testPostSendsCorrectMethodHeadersAndBody(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{"id": 1}', 201);

        $result = $transport->post('work_packages', ['subject' => 'New task']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/work_packages', (string) $request->getUri());
        self::assertSame('application/hal+json', $request->getHeaderLine('Accept'));
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $request->getHeaderLine('Authorization'),
        );
        self::assertSame('{"subject":"New task"}', (string) $request->getBody());
        self::assertSame(1, $result['id']);
    }

    public function testPostWithEmptyBodySendsJsonObject(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}', 200);

        $transport->post('some/path');

        self::assertSame('{}', (string) $this->lastRequest()->getBody());
    }

    public function testPostAppliesQueryParameters(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{}', 200);

        $transport->post('work_packages', [], ['notify' => 'false']);

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('notify=false', $uri);
    }

    public function testPatchSendsCorrectMethodAndBody(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{"id": 42, "subject": "Updated"}', 200);

        $result = $transport->patch('work_packages/42', ['subject' => 'Updated']);

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame('http://example.test/api/v3/work_packages/42', (string) $request->getUri());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame('{"subject":"Updated"}', (string) $request->getBody());
        self::assertSame('Updated', $result['subject']);
    }

    public function testPostMultipartSendsMultipartBodyAndReturnsDecodedResponse(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{"id": 7}', 201);

        $result = $transport->postMultipart('attachments', [
            ['name' => 'metadata', 'contents' => '{"fileName":"a.txt"}', 'contentType' => 'application/json'],
            [
                'name' => 'file',
                'contents' => (new HttpFactory())->createStream('DATA'),
                'contentType' => 'text/plain',
                'filename' => 'a.txt',
            ],
        ]);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/attachments', (string) $request->getUri());
        self::assertSame('application/hal+json', $request->getHeaderLine('Accept'));
        self::assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));
        self::assertSame(
            'Basic ' . base64_encode('apikey:secret-token'),
            $request->getHeaderLine('Authorization'),
        );

        $body = (string) $request->getBody();
        self::assertStringContainsString('name="metadata"', $body);
        self::assertStringContainsString('name="file"', $body);
        self::assertStringContainsString('filename="a.txt"', $body);
        self::assertStringContainsString('DATA', $body);
        self::assertSame(7, $result['id']);
    }

    public function testPostMultipartReadsSeekableStreamFromTheStart(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('{"id": 7}', 201);
        $stream = (new HttpFactory())->createStream('DATA');
        $stream->getContents();

        $transport->postMultipart('attachments', [
            ['name' => 'file', 'contents' => $stream, 'contentType' => 'text/plain', 'filename' => 'a.txt'],
        ]);

        self::assertStringContainsString("\r\n\r\nDATA\r\n", (string) $this->lastRequest()->getBody());
    }

    public function testPostMultipartRejectsNonReadableStreamWithoutSending(): void
    {
        $transport = $this->createTransport('http://example.test');
        $stream = FnStream::decorate((new HttpFactory())->createStream('DATA'), [
            'isReadable' => static fn (): bool => false,
        ]);

        try {
            $transport->postMultipart('attachments', [
                ['name' => 'file', 'contents' => $stream, 'contentType' => 'text/plain', 'filename' => 'a.txt'],
            ]);
            self::fail('Expected an OpenProjectException.');
        } catch (OpenProjectException $exception) {
            self::assertSame(OpenProjectException::class, $exception::class);
            self::assertSame('The stream for multipart part "file" is not readable.', $exception->getMessage());
        }
        self::assertSame([], $this->requestHistory);
    }

    public function testPostMultipartWrapsStreamReadFailure(): void
    {
        $transport = $this->createTransport('http://example.test');
        $previous = new \RuntimeException('Disk read failed.');
        $stream = FnStream::decorate((new HttpFactory())->createStream('DATA'), [
            'read' => static fn (): never => throw $previous,
        ]);

        try {
            $transport->postMultipart('attachments', [
                ['name' => 'file', 'contents' => $stream, 'contentType' => 'text/plain', 'filename' => 'a.txt'],
            ]);
            self::fail('Expected an OpenProjectException.');
        } catch (OpenProjectException $exception) {
            self::assertSame(OpenProjectException::class, $exception::class);
            self::assertSame(
                'Could not read the multipart request body: Disk read failed.',
                $exception->getMessage(),
            );
            self::assertSame($previous, $exception->getPrevious());
        }
        self::assertSame([], $this->requestHistory);
    }

    public function testDeleteSendsCorrectMethodAndReturnsVoid(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('', 204);

        $transport->delete('work_packages/42');

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('http://example.test/api/v3/work_packages/42', (string) $request->getUri());
    }

    public function testMapsConflictResponseToConflictException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-409-conflict.json', 409);

        try {
            $transport->patch('work_packages/42', ['lockVersion' => 0]);
            self::fail('Expected a ConflictException.');
        } catch (ConflictException $exception) {
            self::assertSame(409, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:UpdateConflict',
                $exception->errorIdentifier,
            );
        }
    }

    public function testMapsSingleValidationErrorToValidationException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-422-single.json', 422);

        try {
            $transport->post('work_packages', ['subject' => '']);
            self::fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->httpStatus);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:PropertyConstraintViolation',
                $exception->errorIdentifier,
            );
            self::assertCount(1, $exception->errors);
            self::assertSame('subject', $exception->errors[0]->attribute);
            self::assertSame("Subject can't be blank.", $exception->errors[0]->message);
            self::assertSame(
                'urn:openproject-org:api:v3:errors:PropertyConstraintViolation',
                $exception->errors[0]->identifier,
            );
        }
    }

    public function testMapsMultipleValidationErrorsToValidationException(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueFixtureResponse('error-422-multiple.json', 422);

        try {
            $transport->post('work_packages', []);
            self::fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->httpStatus);
            self::assertCount(2, $exception->errors);
            self::assertSame('subject', $exception->errors[0]->attribute);
            self::assertSame('startDate', $exception->errors[1]->attribute);
        }
    }

    public function testMaps422WithNonJsonBodyToValidationExceptionWithEmptyErrors(): void
    {
        $transport = $this->createTransport('http://example.test');
        $this->queueJsonResponse('<html>...</html>', 422);

        try {
            $transport->post('work_packages', []);
            self::fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->httpStatus);
            self::assertNull($exception->errorIdentifier);
            self::assertSame([], $exception->errors);
        }
    }

    public function testMaps422WithNonObjectJsonBodyProducesEmptyErrors(): void
    {
        $transport = $this->createTransport('http://example.test');
        // Valid JSON but not an object (a bare integer): hits the non-array branch in parseValidationErrors.
        $this->queueJsonResponse('42', 422);

        try {
            $transport->post('work_packages', []);
            self::fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->httpStatus);
            self::assertSame([], $exception->errors);
        }
    }

    public function testMaps422WithJunkEmbeddedErrorsSkipsNonArrayElement(): void
    {
        $transport = $this->createTransport('http://example.test');
        $body = json_encode([
            '_type' => 'Error',
            'errorIdentifier' => 'urn:openproject-org:api:v3:errors:MultipleErrors',
            'message' => 'Multiple field constraints have been violated.',
            '_embedded' => [
                'errors' => [
                    'not-an-array',
                    [
                        '_type' => 'Error',
                        'errorIdentifier' => 'urn:openproject-org:api:v3:errors:PropertyConstraintViolation',
                        'message' => "Subject can't be blank.",
                        '_embedded' => ['details' => ['attribute' => 'subject']],
                    ],
                ],
            ],
        ]);
        $this->queueJsonResponse((string) $body, 422);

        try {
            $transport->post('work_packages', []);
            self::fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            self::assertCount(1, $exception->errors);
            self::assertSame('subject', $exception->errors[0]->attribute);
        }
    }

    private function createTransport(string $baseUrl): Transport
    {
        $httpFactory = new HttpFactory();

        return new Transport(
            $baseUrl,
            new ApiKeyAuthentication('secret-token'),
            $this->createMockClient(),
            $httpFactory,
            $httpFactory,
        );
    }
}
