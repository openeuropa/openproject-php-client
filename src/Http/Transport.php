<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Http;

use OpenEuropa\OpenProjectClient\Authentication\AuthenticationInterface;
use OpenEuropa\OpenProjectClient\Exception\ApiErrorException;
use OpenEuropa\OpenProjectClient\Exception\ApiUnreachableException;
use OpenEuropa\OpenProjectClient\Exception\AuthenticationException;
use OpenEuropa\OpenProjectClient\Exception\ForbiddenException;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
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
     *   On a network failure, a non-2xx response, or an invalid JSON body.
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
            throw new ApiUnreachableException(
                sprintf('Could not reach the OpenProject API: %s', $exception->getMessage()),
                previous: $exception,
            );
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw $this->mapErrorResponse($status, (string) $response->getBody());
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

    /**
     * Maps a non-2xx response to the matching exception.
     */
    private function mapErrorResponse(int $status, string $body): OpenProjectException
    {
        [$identifier, $apiMessage] = $this->parseErrorBody($body);
        $message = sprintf(
            'The OpenProject API responded with HTTP status %d%s',
            $status,
            $apiMessage !== null ? ': ' . $apiMessage : '.',
        );

        return match (true) {
            $status === 401 => new AuthenticationException($message, $status, $identifier),
            $status === 403 => new ForbiddenException($message, $status, $identifier),
            $status === 404 => new NotFoundException($message, $status, $identifier),
            $status >= 500 => new ApiUnreachableException($message, $status),
            default => new ApiErrorException($message, $status, $identifier),
        };
    }

    /**
     * Extracts `errorIdentifier` and `message` from a HAL Error body.
     *
     * @return array{0: ?string, 1: ?string}
     *   The error identifier and message, each null when absent (including
     *   when the body is not a JSON object).
     */
    private function parseErrorBody(string $body): array
    {
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [null, null];
        }
        if (!is_array($decoded)) {
            return [null, null];
        }

        $identifier = $decoded['errorIdentifier'] ?? null;
        $message = $decoded['message'] ?? null;

        return [
            is_string($identifier) ? $identifier : null,
            is_string($message) ? $message : null,
        ];
    }
}
