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
use Psr\Http\Message\StreamInterface;

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

    public function testGetReturnsAttachmentAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('attachment.json');

        $attachment = $endpoint->get(3);

        self::assertSame(
            'http://example.test/api/v3/attachments/3',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Attachment::class, $attachment);
        self::assertSame(3, $attachment->id);
        self::assertSame('phpclient-test.txt', $attachment->fileName);
        self::assertSame('text/plain', $attachment->contentType);
    }

    public function testDownloadReturnsContentStreamAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueJsonResponse('the file content', 200);

        $stream = $endpoint->download(3);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/attachments/3/content',
            (string) $request->getUri(),
        );
        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame('the file content', (string) $stream);
    }

    public function testUploadPostsMultipartBodyAndReturnsAttachment(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('attachment.json', 201);

        $attachment = $endpoint->upload(
            (new HttpFactory())->createStream('file bytes here'),
            'phpclient-test.txt',
            'text/plain',
            'A description',
        );

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

        $endpoint->upload((new HttpFactory())->createStream('x'), 'f.txt', 'text/plain');

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
            (new HttpFactory())->createStream('file bytes here'),
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

    public function testDeleteSendsDeleteToAttachmentUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueJsonResponse('', 204);

        $endpoint->delete(3);

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/attachments/3',
            (string) $request->getUri(),
        );
    }
}
