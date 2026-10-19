<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * A single name/operator/values filter triple for collection requests.
 */
final readonly class Filter
{
    /**
     * @param list<string>|null $values
     *   Values as strings (IDs as "5", booleans as "t"/"f"). Null for
     *   operators that take no values (null checks, date keywords).
     */
    public function __construct(
        public string $name,
        public FilterOperator $operator,
        public ?array $values,
    ) {
    }

    /**
     * Returns the filter as the array shape expected in a filters JSON array.
     *
     * @internal
     *
     * @return array<string, array{operator: string, values: list<string>|null}>
     */
    public function toArray(): array
    {
        return [$this->name => ['operator' => $this->operator->value, 'values' => $this->values]];
    }
}
