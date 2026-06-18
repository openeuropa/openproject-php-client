<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Endpoint;

use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Endpoint\AbstractEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\UsersEndpoint;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\User;
use OpenEuropa\OpenProjectClient\Tests\Support\MockClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersEndpoint::class)]
#[UsesClass(AbstractEndpoint::class)]
#[UsesClass(Transport::class)]
#[UsesClass(User::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(ApiKeyAuthentication::class)]
final class UsersEndpointTest extends TestCase
{
    use MockClientTrait;

    private function createEndpoint(): UsersEndpoint
    {
        $httpFactory = new HttpFactory();

        return new UsersEndpoint(
            new Transport(
                'http://example.test',
                new ApiKeyAuthentication('secret-token'),
                $this->createMockClient(),
                $httpFactory,
                $httpFactory,
            ),
        );
    }

    public function testGetReturnsUser(): void
    {
        $endpoint = $this->createEndpoint();
        $this->queueFixtureResponse('user.json');

        $user = $endpoint->get(1);

        self::assertSame(
            'http://example.test/api/v3/users/1',
            (string) $this->lastRequest()->getUri(),
        );
        self::assertInstanceOf(User::class, $user);
        self::assertSame(1, $user->id);
        self::assertSame('John Sheppard', $user->name);
    }
}
