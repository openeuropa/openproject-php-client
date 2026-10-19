<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * Non-filter query parameters for paginated collection requests.
 */
final readonly class CollectionParams
{
    /**
     * @param int|null $offset
     *   1-based page number.
     * @param int|null $pageSize
     *   Number of results per page.
     * @param list<array{0: string, 1: string}>|null $sortBy
     *   Sort criteria as pairs, e.g. [['id', 'asc']].
     * @param string|null $groupBy
     *   Field name to group results by.
     * @param bool|null $showSums
     *   Whether to include column sums.
     */
    public function __construct(
        public ?int $offset = null,
        public ?int $pageSize = null,
        public ?array $sortBy = null,
        public ?string $groupBy = null,
        public ?bool $showSums = null,
    ) {
    }

    /**
     * Returns a copy with the offset replaced.
     */
    public function withOffset(int $offset): self
    {
        return new self($offset, $this->pageSize, $this->sortBy, $this->groupBy, $this->showSums);
    }

    /**
     * Returns the set parameters as a query string key-value map.
     *
     * @internal
     *
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->offset !== null) {
            $params['offset'] = (string) $this->offset;
        }
        if ($this->pageSize !== null) {
            $params['pageSize'] = (string) $this->pageSize;
        }
        if ($this->sortBy !== null) {
            $params['sortBy'] = (string) json_encode($this->sortBy);
        }
        if ($this->groupBy !== null) {
            $params['groupBy'] = $this->groupBy;
        }
        if ($this->showSums !== null) {
            $params['showSums'] = $this->showSums ? 't' : 'f';
        }

        return $params;
    }
}
