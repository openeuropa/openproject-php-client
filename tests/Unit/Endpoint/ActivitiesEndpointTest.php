<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ActivitiesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Activity;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActivitiesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(ApiKeyAuthentication::class)]
#[UsesClass(Activity::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(PageResults::class)]
#[UsesClass(HalResource::class)]
final class ActivitiesEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): ActivitiesEndpoint
    {
        $httpFactory = new HttpFactory();

        return new ActivitiesEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testAllByWorkPackageReturnsPageResultsAndSendsToCorrectUri(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('work-package-activities.json');

        $results = $endpoint->allByWorkPackage(87);

        self::assertSame(
            'http://example.test/api/v3/work_packages/87/activities',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(PageResults::class, $results);
        self::assertSame(3, $results->total);
        self::assertSame(3, $results->count);
        self::assertInstanceOf(Activity::class, $results->elements[0]);
        self::assertSame(154, $results->elements[0]->id);
        self::assertSame('phpclient integration test comment', $results->elements[0]->comment?->raw);
        self::assertSame(155, $results->elements[1]->id);
        self::assertSame(156, $results->elements[2]->id);
    }

    public function testCommentPostsToCorrectUriWithBodyAndReturnsActivity(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('activity.json', 201);

        $activity = $endpoint->comment(87, 'A fresh standalone comment for the activity fixture');

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/api/v3/work_packages/87/activities', $request->getUri()->getPath());
        self::assertStringContainsString('notify=true', (string) $request->getUri());
        self::assertSame(
            '{"comment":{"raw":"A fresh standalone comment for the activity fixture"}}',
            (string) $request->getBody(),
        );
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(156, $activity->id);
    }

    public function testCommentWithNotifyFalseSendsNotifyFalseQueryParam(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('activity.json', 201);

        $endpoint->comment(87, 'A fresh standalone comment for the activity fixture', false);

        self::assertStringContainsString('notify=false', (string) $this->lastRequest()->getUri());
    }
}
