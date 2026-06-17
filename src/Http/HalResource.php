<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Http;

use OpenEuropa\OpenProjectClient\Model\Link;

/**
 * Thin read helper over a decoded HAL response array.
 */
final class HalResource
{
    /**
     * @param array<string, mixed> $data
     *   The decoded HAL response body.
     */
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Returns the string value at $key, or null when absent or not a string.
     */
    public function getString(string $key): ?string
    {
        $value = $this->data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * Returns the int value at $key, or null when absent or not an int.
     */
    public function getInt(string $key): ?int
    {
        $value = $this->data[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * Returns the array value at $key, or an empty array when absent or not an array.
     *
     * @return array<int|string, mixed>
     */
    public function getArray(string $key): array
    {
        $value = $this->data[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    /**
     * Returns the Link for the given relation, or null when absent.
     */
    public function link(string $rel): ?Link
    {
        $links = $this->data['_links'] ?? null;
        if (!is_array($links)) {
            return null;
        }

        $entry = $links[$rel] ?? null;
        if (!is_array($entry)) {
            return null;
        }

        /** @var array<string, mixed> $entry */
        return Link::fromApiResponse($entry);
    }
}
