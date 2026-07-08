<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\AttachmentsEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Attachment;
use OpenEuropa\OpenProjectClient\Model\AttachmentDigest;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttachmentsEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(Attachment::class)]
#[UsesClass(AttachmentDigest::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(HalResource::class)]
final class AttachmentsEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): AttachmentsEndpoint
    {
        $httpFactory = new HttpFactory();

        return new AttachmentsEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testUploadPostsMultipartBodyAndReturnsAttachment(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('attachment.json', 201);

        $attachment = $endpoint->upload('file bytes here', 'phpclient-test.txt', 'text/plain', 'A description');

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/attachments', (string) $request->getUri());
        self::assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));

        $body = (string) $request->getBody();
        self::assertStringContainsString('name="metadata"', $body);
        self::assertStringContainsString('Content-Type: application/json', $body);
        self::assertStringContainsString('"fileName":"phpclient-test.txt"', $body);
        self::assertStringContainsString('"description":{"raw":"A description"}', $body);
        self::assertStringContainsString('name="file"', $body);
        self::assertStringContainsString('filename="phpclient-test.txt"', $body);
        self::assertStringContainsString('Content-Type: text/plain', $body);
        self::assertStringContainsString('file bytes here', $body);
        self::assertLessThan(strpos($body, 'name="file"'), strpos($body, 'name="metadata"'));

        self::assertSame(3, $attachment->id);
        self::assertSame('phpclient-test.txt', $attachment->fileName);
    }

    public function testUploadWithoutDescriptionOmitsDescriptionFromMetadata(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('attachment.json', 201);

        $endpoint->upload('x', 'f.txt', 'text/plain');

        $body = (string) $this->lastRequest()->getBody();
        self::assertStringContainsString('"fileName":"f.txt"', $body);
        self::assertStringNotContainsString('description', $body);
    }

    public function testUploadToWorkPackagePostsMultipartBodyAndReturnsAttachment(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('attachment.json', 201);

        $attachment = $endpoint->uploadToWorkPackage(
            42,
            'file bytes here',
            'phpclient-test.txt',
            'text/plain',
            'A description',
        );

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/work_packages/42/attachments',
            (string) $request->getUri(),
        );
        self::assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));

        $body = (string) $request->getBody();
        self::assertStringContainsString('name="metadata"', $body);
        self::assertStringContainsString('Content-Type: application/json', $body);
        self::assertStringContainsString('"fileName":"phpclient-test.txt"', $body);
        self::assertStringContainsString('"description":{"raw":"A description"}', $body);
        self::assertStringContainsString('name="file"', $body);
        self::assertStringContainsString('filename="phpclient-test.txt"', $body);
        self::assertStringContainsString('Content-Type: text/plain', $body);
        self::assertStringContainsString('file bytes here', $body);
        self::assertLessThan(strpos($body, 'name="file"'), strpos($body, 'name="metadata"'));

        self::assertSame(3, $attachment->id);
        self::assertSame('phpclient-test.txt', $attachment->fileName);
    }
}
