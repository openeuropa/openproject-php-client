<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Http;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use OpenEuropa\OpenProjectClient\Authentication\AuthenticationInterface;
use OpenEuropa\OpenProjectClient\Exception\ApiErrorException;
use OpenEuropa\OpenProjectClient\Exception\ApiUnreachableException;
use OpenEuropa\OpenProjectClient\Exception\AuthenticationException;
use OpenEuropa\OpenProjectClient\Exception\ConflictException;
use OpenEuropa\OpenProjectClient\Exception\ForbiddenException;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;
use OpenEuropa\OpenProjectClient\Exception\ValidationException;
use OpenEuropa\OpenProjectClient\Model\ValidationError;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @param array<string, string> $query
     *   Query parameters to append to the request.
     *
     * @return array<string, mixed>
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   On a network failure, a non-2xx response, or an invalid JSON body.
     */
    public function get(string $path = '', array $query = []): array
    {
        $request = $this->requestFactory
            ->createRequest('GET', $this->apiRoot . ltrim($path, '/'))
            ->withHeader('Accept', 'application/hal+json');
        $request = $this->applyQuery($request, $query);
        $request = $this->authentication->authenticate($request);

        return $this->decodeBody($this->dispatch($request));
    }

    /**
     * Performs an authenticated POST with a JSON body and returns the decoded response.
     *
     * @param string $path
     *   Resource path relative to the API root.
     * @param array<string, mixed> $body
     *   The request payload; an empty array serializes to the JSON object "{}".
     * @param array<string, string> $query
     *   Query parameters to append to the request.
     *
     * @return array<string, mixed>
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   On a network failure, a non-2xx response, or an invalid JSON body.
     */
    public function post(string $path, array $body = [], array $query = []): array
    {
        $request = $this->buildWriteRequest('POST', $path, $body, $query);

        return $this->decodeBody($this->dispatch($request));
    }

    /**
     * Performs an authenticated PATCH with a JSON body and returns the decoded response.
     *
     * @param string $path
     *   Resource path relative to the API root.
     * @param array<string, mixed> $body
     *   The request payload; an empty array serializes to the JSON object "{}".
     * @param array<string, string> $query
     *   Query parameters to append to the request.
     *
     * @return array<string, mixed>
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   On a network failure, a non-2xx response, or an invalid JSON body.
     */
    public function patch(string $path, array $body = [], array $query = []): array
    {
        $request = $this->buildWriteRequest('PATCH', $path, $body, $query);

        return $this->decodeBody($this->dispatch($request));
    }

    /**
     * Performs an authenticated multipart/form-data POST and returns the decoded response.
     *
     * @param string $path
     *   Resource path relative to the API root.
     * @param list<array{name: string, contents: string, contentType: string, filename?: string}> $parts
     *   The ordered multipart parts. Each part carries an explicit content type so
     *   the builder never falls back to filename-based MIME detection.
     *
     * @return array<string, mixed>
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   On a network failure, a non-2xx response, or an invalid JSON body.
     */
    public function postMultipart(string $path, array $parts): array
    {
        $builder = new MultipartStreamBuilder($this->streamFactory);
        foreach ($parts as $part) {
            $options = ['headers' => ['Content-Type' => $part['contentType']]];
            if (isset($part['filename'])) {
                $options['filename'] = $part['filename'];
            }
            $builder->addResource($part['name'], $part['contents'], $options);
        }
        $stream = $builder->build();

        $request = $this->requestFactory
            ->createRequest('POST', $this->apiRoot . ltrim($path, '/'))
            ->withHeader('Accept', 'application/hal+json')
            ->withHeader('Content-Type', 'multipart/form-data; boundary=' . $builder->getBoundary())
            ->withBody($stream);
        $request = $this->authentication->authenticate($request);

        return $this->decodeBody($this->dispatch($request));
    }

    /**
     * Performs an authenticated DELETE and returns nothing.
     *
     * @param string $path
     *   Resource path relative to the API root.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   On a network failure or a non-2xx response.
     */
    public function delete(string $path): void
    {
        $request = $this->requestFactory
            ->createRequest('DELETE', $this->apiRoot . ltrim($path, '/'))
            ->withHeader('Accept', 'application/hal+json');
        $request = $this->authentication->authenticate($request);

        // OpenProject answers with 204 No Content, so there is nothing to decode.
        $this->dispatch($request);
    }

    /**
     * Builds a POST or PATCH request with a JSON body and the standard headers.
     *
     * @param array<string, mixed> $body
     * @param array<string, string> $query
     */
    private function buildWriteRequest(string $method, string $path, array $body, array $query): RequestInterface
    {
        $json = $body === [] ? '{}' : json_encode($body, JSON_THROW_ON_ERROR);
        $request = $this->requestFactory
            ->createRequest($method, $this->apiRoot . ltrim($path, '/'))
            ->withHeader('Accept', 'application/hal+json')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream($json));
        $request = $this->applyQuery($request, $query);

        return $this->authentication->authenticate($request);
    }

    /**
     * Sends the request, throwing on a network failure or a non-2xx response.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    private function dispatch(RequestInterface $request): ResponseInterface
    {
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

        return $response;
    }

    /**
     * Decodes the response body as a JSON object.
     *
     * @return array<string, mixed>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   When the body is not valid JSON or is not a JSON object.
     */
    private function decodeBody(ResponseInterface $response): array
    {
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
     * Applies query parameters to the request URI using RFC 3986 encoding.
     *
     * @param array<string, string> $query
     */
    private function applyQuery(RequestInterface $request, array $query): RequestInterface
    {
        if ($query === []) {
            return $request;
        }

        return $request->withUri(
            $request->getUri()->withQuery(http_build_query($query, '', '&', PHP_QUERY_RFC3986)),
        );
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
            $status === 409 => new ConflictException($message, $status, $identifier),
            $status === 422 => new ValidationException(
                $message,
                $status,
                $identifier,
                $this->parseValidationErrors($body),
            ),
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

    /**
     * Parses the validation errors from a 422 response body.
     *
     * @return list<ValidationError>
     */
    private function parseValidationErrors(string $body): array
    {
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // An unparsable body yields no errors rather than an exception:
            // throwing here would mask the API error being reported.
            return [];
        }
        if (!is_array($decoded)) {
            return [];
        }
        /** @var array<string, mixed> $decoded */

        $embedded = $decoded['_embedded'] ?? null;
        if (is_array($embedded) && isset($embedded['errors']) && is_array($embedded['errors'])) {
            $errors = [];
            foreach ($embedded['errors'] as $element) {
                if (!is_array($element)) {
                    continue;
                }
                /** @var array<string, mixed> $element */
                $errors[] = ValidationError::fromApiResponse($element);
            }

            return $errors;
        }

        return [ValidationError::fromApiResponse($decoded)];
    }
}
