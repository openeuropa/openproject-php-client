<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Base class for all endpoint classes, providing transport access.
 *
 * @internal
 */
abstract class AbstractEndpoint
{
    /**
     * @internal
     */
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

    /**
     * Fetches one page of a collection with optional filters and params.
     *
     * @template T
     * @param string $path
     *   Collection path relative to the API root.
     * @param callable(array<string, mixed>): T $mapper
     *   Maps each raw element array to a DTO.
     *
     * @return PageResults<T>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    protected function getCollectionPage(
        string $path,
        callable $mapper,
        ?FilterSet $filters = null,
        ?CollectionParams $params = null,
    ): PageResults {
        return PageResults::fromApiResponse(
            $this->transport->get($path, $this->buildQuery($filters, $params)),
            $mapper,
        );
    }

    /**
     * Returns a generator that yields DTOs from all pages of a collection.
     *
     * @template T
     * @param string $path
     *   Collection path relative to the API root.
     * @param callable(array<string, mixed>): T $mapper
     *   Maps each raw element array to a DTO.
     *
     * @return \Generator<int, T>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    protected function paginate(
        string $path,
        callable $mapper,
        ?FilterSet $filters = null,
        ?CollectionParams $params = null,
    ): \Generator {
        $offset = $params !== null ? ($params->offset ?? 1) : 1;
        $params = $params ?? new CollectionParams();

        while (true) {
            $page = $this->getCollectionPage($path, $mapper, $filters, $params->withOffset($offset));

            foreach ($page->elements as $element) {
                yield $element;
            }

            // A page without this link is the API's signal that no further
            // pages exist.
            if ($page->nextByOffset === null) {
                return;
            }

            $offset++;
        }
    }

    /**
     * Builds the query parameter map from optional filters and collection parameters.
     *
     * @return array<string, string>
     */
    private function buildQuery(?FilterSet $filters, ?CollectionParams $params): array
    {
        $query = [];

        if ($filters !== null) {
            $query['filters'] = $filters->toJson();
        }

        if ($params !== null) {
            $query = array_merge($query, $params->toQueryParams());
        }

        return $query;
    }
}
