<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\WorkPackagesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\CustomFields;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackagesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(WorkPackage::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(CustomFields::class)]
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
final class WorkPackagesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): WorkPackagesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new WorkPackagesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsWorkPackageAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-package.json');

        $workPackage = $endpoint->get(2);

        self::assertSame(
            'http://example.test/api/v3/work_packages/2',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(WorkPackage::class, $workPackage);
        self::assertSame(2, $workPackage->id);
        self::assertSame('Organize open source conference', $workPackage->subject);
    }

    public function testListPageWithNoArgsSendsExplicitEmptyFiltersToBypassOpenOnlyDefault(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-packages.json');

        $results = $endpoint->listPage();

        self::assertStringContainsString(
            'filters=%5B%5D',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(3, $results->total);
        self::assertSame(2, $results->count);
    }

    public function testAllGeneratorPaginatesAcrossTwoPagesAndTerminates(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-packages.json');
        $this->queueFixtureResponse('work-packages-page2.json');

        $workPackages = iterator_to_array($endpoint->all());

        self::assertCount(3, $workPackages);
        self::assertInstanceOf(WorkPackage::class, $workPackages[0]);
        self::assertSame(1, $workPackages[0]->id);
        self::assertSame(3, $workPackages[2]->id);

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('offset=2', $lastUri);
    }

    public function testAllWithNoArgsSendsExplicitEmptyFiltersToBypassOpenOnlyDefault(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-packages.json');
        $this->queueFixtureResponse('work-packages-page2.json');

        iterator_to_array($endpoint->all());

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=%5B%5D', $lastUri);
        self::assertStringContainsString('offset=2', $lastUri);
    }

    public function testListPageWithFiltersAndParamsSendsQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-packages.json');

        $results = $endpoint->listPage(
            new FilterSet(new Filter('status', FilterOperator::EQUAL, ['1'])),
            new CollectionParams(pageSize: 2, sortBy: [['id', 'asc']]),
        );

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=', $uri);
        self::assertStringContainsString('pageSize=2', $uri);
        self::assertStringContainsString('sortBy=', $uri);
        self::assertInstanceOf(PageResults::class, $results);
    }

    public function testListByProjectGeneratorPaginatesAcrossTwoPagesAndTerminates(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-packages.json');
        $this->queueFixtureResponse('work-packages-page2.json');

        $workPackages = iterator_to_array($endpoint->listByProject(7));

        self::assertCount(3, $workPackages);
        self::assertInstanceOf(WorkPackage::class, $workPackages[0]);
        self::assertSame(1, $workPackages[0]->id);
        self::assertSame(3, $workPackages[2]->id);

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('projects/7/work_packages', $lastUri);
        self::assertStringContainsString('offset=2', $lastUri);
    }

    public function testListByProjectWithNoArgsSendsExplicitEmptyFiltersToBypassOpenOnlyDefault(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-packages.json');
        $this->queueFixtureResponse('work-packages-page2.json');

        iterator_to_array($endpoint->listByProject(7));

        $lastUri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=%5B%5D', $lastUri);
        self::assertStringContainsString('offset=2', $lastUri);
    }

    public function testCreateFormWithNoProjectSendsPostToGlobalFormUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->createForm();

        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertSame(
            'http://example.test/api/v3/work_packages/form',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Form::class, $form);
        self::assertTrue($form->hasErrors());
    }

    public function testCreateFormWithProjectIdSendsPostToProjectScopedUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->createForm([], 6);

        self::assertSame(
            'http://example.test/api/v3/projects/6/work_packages/form',
            (string) $this->lastRequest()->getUri(),
        );
    }

    public function testCreateFormWithPayloadSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->createForm(['subject' => 'My work package']);

        $request = $this->lastRequest();
        self::assertStringContainsString('"subject":"My work package"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testUpdateFormSendsPostToWorkPackageFormUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->updateForm(42, ['subject' => 'Updated subject']);

        self::assertSame('POST', $this->lastRequest()->getMethod());
        self::assertSame(
            'http://example.test/api/v3/work_packages/42/form',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Form::class, $form);
    }

    public function testUpdateFormWithPayloadSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->updateForm(42, ['subject' => 'Updated subject']);

        $request = $this->lastRequest();
        self::assertStringContainsString('"subject":"Updated subject"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testCreateWithDefaultNotifySendsPostToWorkPackagesUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-package.json', 201);

        $workPackage = $endpoint->create(['subject' => 'Test work package']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/api/v3/work_packages', $request->getUri()->getPath());
        self::assertStringContainsString('notify=true', (string) $request->getUri());
        self::assertInstanceOf(WorkPackage::class, $workPackage);
        self::assertSame(2, $workPackage->id);
        self::assertSame('Organize open source conference', $workPackage->subject);
    }

    public function testCreateWithNotifyFalseSendsNotifyFalseQueryParam(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-package.json', 201);

        $endpoint->create(['subject' => 'Test work package'], false);

        self::assertStringContainsString('notify=false', (string) $this->lastRequest()->getUri());
    }

    public function testCreateSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-package.json', 201);

        $endpoint->create(['subject' => 'Test work package']);

        $request = $this->lastRequest();
        self::assertStringContainsString('"subject":"Test work package"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }
}
