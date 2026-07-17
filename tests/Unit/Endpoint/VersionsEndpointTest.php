<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\VersionsEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use OpenEuropa\OpenProjectClient\Model\Version;
use OpenEuropa\OpenProjectClient\Model\VersionInput;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VersionsEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(Version::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
#[UsesClass(Form::class)]
#[UsesClass(SchemaProperty::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(VersionInput::class)]
final class VersionsEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): VersionsEndpoint
    {
        $httpFactory = new HttpFactory();

        return new VersionsEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsVersionAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('version.json');

        $version = $endpoint->get(3);

        self::assertSame(
            'http://example.test/api/v3/versions/3',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Version::class, $version);
        self::assertSame(3, $version->id);
        self::assertSame('v0.9.1-alpha', $version->name);
    }

    public function testAllReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('versions.json');

        $results = $endpoint->all();

        self::assertSame(
            'http://example.test/api/v3/versions',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(2, $results->total);
        self::assertSame(2, $results->count);
        self::assertInstanceOf(Version::class, $results->elements[0]);
        self::assertSame(3, $results->elements[0]->id);
    }

    public function testAllWithFiltersSendsFiltersQueryParam(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('versions.json');

        $endpoint->all(new FilterSet(new Filter('status', FilterOperator::EQUAL, ['open'])));

        $uri = (string) $this->lastRequest()->getUri();
        self::assertSame('/api/v3/versions', $this->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('filters=', $uri);
    }

    public function testAllByProjectReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('versions.json');

        $results = $endpoint->allByProject(7);

        self::assertSame(
            'http://example.test/api/v3/projects/7/versions',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(2, $results->total);
        self::assertInstanceOf(Version::class, $results->elements[0]);
    }

    public function testCreateFormWithNoPayloadSendsPostToGlobalFormUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->createForm();

        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertSame(
            'http://example.test/api/v3/versions/form',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Form::class, $form);
    }

    public function testCreateFormWithPayloadSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->createForm((new VersionInput())->name('My version'));

        $request = $this->lastRequest();
        self::assertStringContainsString('"name":"My version"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testCreatePostsToVersionsUriAndReturnsVersion(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('version.json', 201);

        $version = $endpoint->create((new VersionInput())->name('My version'));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/versions', (string) $request->getUri());
        self::assertStringContainsString('"name":"My version"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(3, $version->id);
        self::assertSame('v0.9.1-alpha', $version->name);
    }

    public function testUpdateFormPostsToVersionFormUriAndReturnsForm(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->updateForm(3, (new VersionInput())->name('My version'));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/versions/3/form',
            (string) $request->getUri(),
        );
        self::assertStringContainsString('"name":"My version"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertTrue($form->hasErrors());
    }

    public function testUpdatePatchesToVersionUriAndReturnsVersion(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('version.json');

        $version = $endpoint->update(3, (new VersionInput())->name('My version'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/versions/3',
            (string) $request->getUri(),
        );
        self::assertStringContainsString('"name":"My version"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(3, $version->id);
        self::assertSame('v0.9.1-alpha', $version->name);
    }

    public function testDeleteSendsDeleteToVersionUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueJsonResponse('', 204);

        $endpoint->delete(3);

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/versions/3',
            (string) $request->getUri(),
        );
    }
}
