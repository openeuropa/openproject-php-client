<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * One decoded HAL collection page: its counts and mapped elements.
 *
 * @template T
 * @implements \IteratorAggregate<int, T>
 */
final readonly class PageResults implements \IteratorAggregate
{
    /**
     * @internal
     *
     * @param list<T> $elements
     */
    public function __construct(
        public int $total,
        public int $count,
        public array $elements,
        public ?int $pageSize = null,
        public ?int $offset = null,
        public ?string $nextByOffset = null,
    ) {
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * Creates a page from a decoded HAL collection response.
     *
     * @template U
     * @param array<string, mixed> $data
     *   The decoded collection body.
     * @param callable(array<string, mixed>): U $mapper
     *   Maps each raw element array to a DTO.
     *
     * @return self<U>
     */
    public static function fromApiResponse(array $data, callable $mapper): self
    {
        $hal = new HalResource($data);
        $embedded = $hal->getArray('_embedded');
        $rawElements = $embedded['elements'] ?? null;

        $elements = [];
        if (is_array($rawElements)) {
            foreach ($rawElements as $element) {
                if (is_array($element)) {
                    /** @var array<string, mixed> $element */
                    $elements[] = $mapper($element);
                }
            }
        }

        return new self(
            $hal->getInt('total') ?? 0,
            $hal->getInt('count') ?? 0,
            $elements,
            $hal->getInt('pageSize'),
            $hal->getInt('offset'),
            $hal->link('nextByOffset')?->href,
        );
    }
}
