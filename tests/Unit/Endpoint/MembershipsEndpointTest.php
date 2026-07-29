<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\MembershipsEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Form\SchemaProperty;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\Membership;
use OpenEuropa\OpenProjectClient\Model\MembershipInput;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MembershipsEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(Membership::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Link::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(FilterSet::class)]
#[UsesClass(Filter::class)]
#[UsesClass(FilterOperator::class)]
#[UsesClass(CollectionParams::class)]
#[UsesClass(Form::class)]
#[UsesClass(SchemaProperty::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(MembershipInput::class)]
final class MembershipsEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): MembershipsEndpoint
    {
        $httpFactory = new HttpFactory();

        return new MembershipsEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsMembership(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('membership.json');

        $membership = $endpoint->get(11);

        self::assertSame(
            'http://example.test/api/v3/memberships/11',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Membership::class, $membership);
        self::assertSame(11, $membership->id);
    }

    public function testListPageReturnsMembershipsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('memberships.json');

        $results = $endpoint->listPage();

        self::assertSame(
            'http://example.test/api/v3/memberships',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(1, $results->total);
        self::assertSame(1, $results->count);
        self::assertSame(20, $results->pageSize);
        self::assertSame(1, $results->offset);
        self::assertSame('/api/v3/memberships?filters=%5B%5D&offset=2&pageSize=20', $results->nextByOffset);
    }

    public function testListPageWithFiltersAndParamsSendsQueryString(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('memberships.json');

        $results = $endpoint->listPage(
            new FilterSet(new Filter('project', FilterOperator::EQUAL, ['1'])),
            new CollectionParams(pageSize: 20, sortBy: [['id', 'asc']]),
        );

        $uri = (string) $this->lastRequest()->getUri();
        self::assertStringContainsString('filters=', $uri);
        self::assertStringContainsString('pageSize=20', $uri);
        self::assertStringContainsString('sortBy=', $uri);
        self::assertInstanceOf(PageResults::class, $results);
    }

    public function testAllGeneratorPaginatesAcrossTwoPagesAndTerminates(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('memberships.json');
        $this->queueFixtureResponse('memberships-page2.json');

        $memberships = iterator_to_array($endpoint->all());

        self::assertCount(2, $memberships);
        self::assertInstanceOf(Membership::class, $memberships[0]);
        self::assertSame(11, $memberships[0]->id);
        self::assertSame(23, $memberships[1]->id);

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
            'http://example.test/api/v3/memberships/form',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(Form::class, $form);
    }

    public function testCreateFormWithPayloadSendsJsonBodyAndContentTypeHeader(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $endpoint->createForm((new MembershipInput())->project(21));

        $request = $this->lastRequest();
        self::assertStringContainsString('"project":{"href":"\/api\/v3\/projects\/21"}', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testCreatePostsToMembershipsUriAndReturnsMembership(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('membership.json', 201);

        $membership = $endpoint->create((new MembershipInput())->project(21)->user(14)->roles(4));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('http://example.test/api/v3/memberships', (string) $request->getUri());
        self::assertStringContainsString('"roles":[{"href":"\/api\/v3\/roles\/4"}]', (string) $request->getBody());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(11, $membership->id);
    }

    public function testUpdateFormPostsToMembershipFormUriAndReturnsForm(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('form.json');

        $form = $endpoint->updateForm(11, (new MembershipInput())->roles(5, 6));

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/memberships/11/form',
            (string) $request->getUri(),
        );
        self::assertStringContainsString(
            '"roles":[{"href":"\/api\/v3\/roles\/5"},{"href":"\/api\/v3\/roles\/6"}]',
            (string) $request->getBody(),
        );
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertTrue($form->hasErrors());
    }

    public function testUpdatePatchesToMembershipUriAndReturnsMembership(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('membership.json');

        $membership = $endpoint->update(11, (new MembershipInput())->roles(5, 6));

        $request = $this->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/memberships/11',
            (string) $request->getUri(),
        );
        self::assertStringContainsString(
            '"roles":[{"href":"\/api\/v3\/roles\/5"},{"href":"\/api\/v3\/roles\/6"}]',
            (string) $request->getBody(),
        );
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(11, $membership->id);
    }

    public function testDeleteSendsDeleteToMembershipUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueJsonResponse('', 204);

        $endpoint->delete(11);

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame(
            'http://example.test/api/v3/memberships/11',
            (string) $request->getUri(),
        );
    }
}
