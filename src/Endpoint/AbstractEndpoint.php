<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\PageResults;

/**
 * Base class for all endpoint classes, providing transport access.
 */
abstract class AbstractEndpoint
{
    public function __construct(protected readonly Transport $transport)
    {
    }

    /**
     * Decodes a HAL collection into a PageResults of mapped DTOs.
     *
     * @template T
     * @param string $path
     *   Collection path relative to the API root, e.g. "statuses".
     * @param callable(array<string, mixed>): T $mapper
     *   Maps each raw element array to a DTO.
     *
     * @return PageResults<T>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    protected function getCollection(string $path, callable $mapper): PageResults
    {
        return PageResults::fromApiResponse($this->transport->get($path), $mapper);
    }
}
