<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\TimeEntriesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\CustomFields;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\TimeEntry;
use OpenEuropa\OpenProjectClient\Model\TimeEntryInput;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntriesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(TimeEntry::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(CustomFields::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
#[UsesClass(CollectionParams::class)]
#[UsesClass(Form::class)]
#[UsesClass(SchemaProperty::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(TimeEntryInput::class)]
final class TimeEntriesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): TimeEntriesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new TimeEntriesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsTimeEntry(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('time-entry.json');

        $timeEntry = $endpoint->get(42);

        self::assertSame(
            'http://example.test/api/v3/time_entries/42',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(TimeEntry::class, $timeEntry);
        self::assertSame(42, $timeEntry->id);
    }

    public function testListPageReturnsTimeEntriesAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('time-entries.json');

        $results = $endpoint->listPage();

        self::assertSame(
            'http://example.test/api/v3/time_entries',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(39, $results->total);
        self::assertSame(2, $results->count);
        self::assertSame(2, $results->pageSize);
        self::assertSame(1, $results->offset);
        self::assertSame('/api/v3/time_entries?offset=2&pageSize=2', $results->nextByOffset);
    }

    public function testListPageWithFiltersAndParamsSendsQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('time-entries.json');

        $results = $endpoint->listPage(
            new FilterSet(new Filter('project_id', FilterOperator::EQUAL, ['1'])),
            new CollectionParams(pageSize: 2, sortBy: [['spent_on', 'asc']]),
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
        $this->queueFixtureResponse('time-entries.json');
        $this->queueFixtureResponse('time-entries-page2.json');

        $timeEntries = iterator_to_array($endpoint->all());

        self::assertCount(3, $timeEntries);
        self::assertInstanceOf(TimeEntry::class, $timeEntries[0]);
        self::assertSame(5, $timeEntries[0]->id);
        self::assertSame(15, $timeEntries[2]->id);

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
            'http://example.test/api/v3/time_entries/form',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Form::class, $form);
    }

    public function testCreateFormWithPayloadSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->createForm((new TimeEntryInput())->hours('PT2H'));

        $request = $this->lastRequest();
        self::assertStringContainsString('"hours":"PT2H"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testCreatePostsToTimeEntriesUriAndReturnsTimeEntry(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('time-entry.json', 201);

        $timeEntry = $endpoint->create((new TimeEntryInput())->hours('PT2H'));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/time_entries', (string) $request->getUri());
        self::assertStringContainsString('"hours":"PT2H"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(42, $timeEntry->id);
    }

    public function testUpdateFormPostsToTimeEntryFormUriAndReturnsForm(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->updateForm(42, (new TimeEntryInput())->hours('PT2H'));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/time_entries/42/form',
            (string) $request->getUri(),
        );
        self::assertStringContainsString('"hours":"PT2H"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertTrue($form->hasErrors());
    }

    public function testUpdatePatchesToTimeEntryUriAndReturnsTimeEntry(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('time-entry.json');

        $timeEntry = $endpoint->update(42, (new TimeEntryInput())->hours('PT2H'));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/time_entries/42',
            (string) $request->getUri(),
        );
        self::assertStringContainsString('"hours":"PT2H"', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(42, $timeEntry->id);
    }

    public function testDeleteSendsDeleteToTimeEntryUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueJsonResponse('', 204);

        $endpoint->delete(42);

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/time_entries/42',
            (string) $request->getUri(),
        );
    }
}
