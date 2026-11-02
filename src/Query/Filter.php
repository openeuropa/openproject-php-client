<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * A single name/operator/values filter triple for collection requests.
 */
final readonly class Filter
{
    /**
     * @param FilterOperator|string $operator
     *   A string is sent as is. Use one only for an operator the enum has no
     *   case for, such as one a plugin registers.
     * @param list<string>|null $values
     *   Values as strings (IDs as "5", booleans as "t"/"f"). Null for
     *   operators that take no values (null checks, date keywords).
     */
    public function __construct(
        public string $name,
        public FilterOperator|string $operator,
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
        $operator = $this->operator instanceof FilterOperator ? $this->operator->value : $this->operator;

        return [$this->name => ['operator' => $operator, 'values' => $this->values]];
    }
}
