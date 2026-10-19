<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * An ordered set of ANDed filters, serialized to the "filters" query param.
 */
final readonly class FilterSet
{
    /** @var list<Filter> */
    private array $filters;

    public function __construct(Filter ...$filters)
    {
        $this->filters = array_values($filters);
    }

    /**
     * Returns the filters as a JSON-encoded array for the "filters" query param.
     *
     * An empty set serializes to "[]".
     *
     * @internal
     */
    public function toJson(): string
    {
        return (string) json_encode(array_map(static fn(Filter $f) => $f->toArray(), $this->filters));
    }
}
