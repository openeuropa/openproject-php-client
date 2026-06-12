<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Support;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Wires Guzzle's MockHandler as a PSR-18 client, with on-the-wire capture.
 */
trait MockClientTrait
{
    private MockHandler $mockHandler;

    /**
     * Guzzle history middleware entries; loosely typed because the
     * middleware takes the container by reference as array|ArrayAccess.
     *
     * @var array<int, mixed>
     */
    private array $requestHistory = [];

    /**
     * Creates a PSR-18 client whose responses are served from the queue.
     */
    private function createMockClient(): ClientInterface
    {
        $this->mockHandler = new MockHandler();
        $this->requestHistory = [];
        $handlerStack = HandlerStack::create($this->mockHandler);
        // The by-ref container param is typed array|ArrayAccess, but the
        // middleware only ever appends entries to our array.
        // @phpstan-ignore assign.propertyType
        $handlerStack->push(Middleware::history($this->requestHistory));

        return new GuzzleClient(['handler' => $handlerStack]);
    }

    /**
     * Queues a HAL+JSON response with the given body.
     */
    private function queueJsonResponse(string $body, int $status = 200): void
    {
        $this->mockHandler->append(
            new Response($status, ['Content-Type' => 'application/hal+json; charset=utf-8'], $body),
        );
    }

    /**
     * Queues a HAL+JSON response with the given fixture file as body.
     */
    private function queueFixtureResponse(string $fixture, int $status = 200): void
    {
        $this->queueJsonResponse($this->loadFixture($fixture), $status);
    }

    /**
     * Returns the content of a file in tests/fixtures.
     */
    private function loadFixture(string $fixture): string
    {
        $path = __DIR__ . '/../fixtures/' . $fixture;
        $content = @file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException(sprintf('Fixture "%s" not found.', $path));
        }

        return $content;
    }

    /**
     * Returns the last request sent through the mock client.
     */
    private function lastRequest(): RequestInterface
    {
        $entry = end($this->requestHistory);
        $request = is_array($entry) ? ($entry['request'] ?? null) : null;
        if (!$request instanceof RequestInterface) {
            throw new \RuntimeException('No request was sent through the mock client.');
        }

        return $request;
    }
}
