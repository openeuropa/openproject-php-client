<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\WorkPackagesEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkPackagesEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(WorkPackage::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(Formattable::class)]
#[UsesClass(Link::class)]
#[UsesClass(ApiKeyAuthentication::class)]
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
}
