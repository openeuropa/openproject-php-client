<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Http;

use OpenEuropa\OpenProjectClient\Authentication\AuthenticationInterface;
use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * The single transport: builds PSR-7 requests, sends them, decodes responses.
 *
 * This is the only place that touches PSR-7/PSR-18 and the only place that
 * throws on transport or API errors; endpoints and DTOs never see either.
 */
final class Transport
{
    /**
     * The API root URL, normalized to a trailing slash.
     */
    private readonly string $apiRoot;

    public function __construct(
        string $baseUrl,
        private readonly AuthenticationInterface $authentication,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        // Unused until the write flows land; injected from the start so the
        // constructor signature stays stable.
        // @phpstan-ignore property.onlyWritten
        private readonly StreamFactoryInterface $streamFactory,
    ) {
        $this->apiRoot = rtrim($baseUrl, '/') . '/api/v3/';
    }

    /**
     * Performs an authenticated GET and returns the decoded JSON body.
     *
     * @param string $path
     *   Resource path relative to the API root, e.g. "work_packages/42". An
     *   empty string addresses the API root itself.
     *
     * @return array<string, mixed>
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   When the request fails on the network, the API responds with a
     *   non-2xx status, or the body is not valid JSON.
     */
    public function get(string $path = ''): array
    {
        $request = $this->requestFactory
            ->createRequest('GET', $this->apiRoot . ltrim($path, '/'))
            ->withHeader('Accept', 'application/hal+json');
        $request = $this->authentication->authenticate($request);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new OpenProjectException(
                sprintf('Request to the OpenProject API failed: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new OpenProjectException(
                sprintf('The OpenProject API responded with HTTP status %d.', $status),
                $status,
            );
        }

        try {
            $decoded = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new OpenProjectException(
                'The OpenProject API responded with a body that is not valid JSON.',
                0,
                $exception,
            );
        }
        if (!is_array($decoded)) {
            throw new OpenProjectException('The OpenProject API responded with a non-object JSON body.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
