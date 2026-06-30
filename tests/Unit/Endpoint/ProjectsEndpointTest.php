<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ProjectsEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Project;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectsEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Project::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Form::class)]
#[UsesClass(SchemaProperty::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(Link::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
#[UsesClass(CollectionParams::class)]
#[UsesClass(ProjectInput::class)]
final class ProjectsEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): ProjectsEndpoint
    {
        $httpFactory = new HttpFactory();

        return new ProjectsEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsProjectAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('project.json');

        $project = $endpoint->get(1);

        self::assertSame(
            'http://example.test/api/v3/projects/1',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Project::class, $project);
        self::assertSame(1, $project->id);
        self::assertSame('Project example', $project->name);
    }

    public function testListPageWithNoArgsHitsProjectsUriWithNoQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('projects.json');

        $results = $endpoint->listPage();

        self::assertSame(
            'http://example.test/api/v3/projects',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(3, $results->total);
        self::assertSame(2, $results->count);
        self::assertSame(2, $results->pageSize);
        self::assertSame(1, $results->offset);
        self::assertSame('/api/v3/projects?offset=2&pageSize=2', $results->nextByOffset);
    }

    public function testListPageWithFiltersAndParamsSendsQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('projects.json');

        $results = $endpoint->listPage(
            new FilterSet(new Filter('active', FilterOperator::EQUAL, ['t'])),
            new CollectionParams(pageSize: 2, sortBy: [['name', 'asc']]),
        );

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=', $uri);
        self::assertStringContainsString('pageSize=2', $uri);
        self::assertStringContainsString('sortBy=', $uri);
        self::assertInstanceOf(PageResults::class, $results);
    }

    public function testAllGeneratorPaginatesAcrossTwoPagesAndTerminates(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('projects.json');
        $this->queueFixtureResponse('projects-page2.json');

        $projects = iterator_to_array($endpoint->all());

        self::assertCount(3, $projects);
        self::assertInstanceOf(Project::class, $projects[0]);
        self::assertSame(1, $projects[0]->id);
        self::assertSame(3, $projects[2]->id);

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('offset=2', $lastUri);
    }

    public function testCreateFormWithNoPayloadSendsPostToGlobalFormUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->createForm();

        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertSame(
            'http://example.test/api/v3/projects/form',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Form::class, $form);
    }

    public function testCreateFormWithPayloadSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->createForm((new ProjectInput())->name('My project'));

        $request = $this->lastRequest();
        self::assertStringContainsString('"name":"My project"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testCreatePostsToProjectsUriAndReturnsProject(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('project.json', 201);

        $project = $endpoint->create((new ProjectInput())->name('My project'));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/projects', (string) $request->getUri());
        self::assertStringContainsString('"name":"My project"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(1, $project->id);
        self::assertSame('Project example', $project->name);
    }

    public function testUpdateFormPostsToProjectFormUriAndReturnsForm(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->updateForm(42, (new ProjectInput())->name('My project'));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/projects/42/form',
            (string) $request->getUri(),
        );
        self::assertStringContainsString('"name":"My project"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertTrue($form->hasErrors());
    }
}
