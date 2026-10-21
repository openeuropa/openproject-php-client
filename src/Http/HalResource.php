<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Http;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use OpenEuropa\OpenProjectClient\Model\Link;

/**
 * Thin read helper over a decoded HAL response array.
 *
 * @internal
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
     * Returns the string value for the given key, or null when absent or not a string.
     */
    public function getString(string $key): ?string
    {
        $value = $this->data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * Returns the int value for the given key, or null when absent or not an int.
     */
    public function getInt(string $key): ?int
    {
        $value = $this->data[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * Returns the int value for a spec-required key, throwing when it is absent or not an int.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public function requireInt(string $key): int
    {
        $value = $this->data[$key] ?? null;
        if (!is_int($value)) {
            throw new UnexpectedResponseException(
                sprintf('Expected required integer field "%s" in API response.', $key),
            );
        }

        return $value;
    }

    /**
     * Returns the string value for a spec-required key, throwing when it is absent or not a string.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public function requireString(string $key): string
    {
        $value = $this->data[$key] ?? null;
        if (!is_string($value)) {
            throw new UnexpectedResponseException(
                sprintf('Expected required string field "%s" in API response.', $key),
            );
        }

        return $value;
    }

    /**
     * Returns the bool value for a spec-required key, throwing when it is absent or not a bool.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public function requireBool(string $key): bool
    {
        $value = $this->data[$key] ?? null;
        if (!is_bool($value)) {
            throw new UnexpectedResponseException(
                sprintf('Expected required boolean field "%s" in API response.', $key),
            );
        }

        return $value;
    }

    /**
     * Returns the bool value for the given key, or null when absent or not a bool.
     */
    public function getBool(string $key): ?bool
    {
        $value = $this->data[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    /**
     * Returns the timestamp for the given key, or null when absent or not an RFC 3339 date-time.
     */
    public function getDateTime(string $key): ?\DateTimeImmutable
    {
        $value = $this->getString($key);
        if ($value === null) {
            return null;
        }

        // RFC 3339 makes fractional seconds optional, and each format accepts only one form.
        return $this->parseDateTime('Y-m-d\TH:i:s.uP', $value)
            ?? $this->parseDateTime(\DateTimeInterface::RFC3339, $value);
    }

    /**
     * Returns the date for the given key, or null when absent or not a YYYY-MM-DD date.
     *
     * The date is set at midnight in PHP's default time zone, since the API sends none.
     */
    public function getDate(string $key): ?\DateTimeImmutable
    {
        $value = $this->getString($key);
        if ($value === null) {
            return null;
        }

        // Without "!", the time fields the format does not parse take the current time.
        return $this->parseDateTime('!Y-m-d', $value);
    }

    /**
     * Returns the duration for the given key, or null when absent or not an ISO 8601 duration.
     */
    public function getDuration(string $key): ?\DateInterval
    {
        $value = $this->getString($key);
        if ($value === null) {
            return null;
        }

        try {
            return new \DateInterval($value);
        } catch (\DateMalformedIntervalStringException) {
            return null;
        }
    }

    /**
     * Returns the array value for the given key, or an empty array when absent or not an array.
     *
     * @return array<int|string, mixed>
     */
    public function getArray(string $key): array
    {
        $value = $this->data[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    /**
     * Returns the Formattable for the given key, or null when absent or without a string format.
     */
    public function formattable(string $key): ?Formattable
    {
        $value = $this->data[$key] ?? null;
        if (!is_array($value) || !is_string($value['format'] ?? null)) {
            return null;
        }

        /** @var array<string, mixed> $value */
        return Formattable::fromApiResponse($value);
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

    /**
     * Returns the Links for the given relation, or an empty list when absent or not an array.
     *
     * @return list<Link>
     */
    public function linkList(string $rel): array
    {
        $links = $this->data['_links'] ?? null;
        if (!is_array($links)) {
            return [];
        }

        $entry = $links[$rel] ?? null;
        if (!is_array($entry)) {
            return [];
        }

        // HAL renders a relation holding one link as a bare object and a
        // relation holding several as an array, so both shapes are valid here.
        if (!array_is_list($entry)) {
            /** @var array<string, mixed> $entry */
            return [Link::fromApiResponse($entry)];
        }

        $list = [];
        foreach ($entry as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $list[] = Link::fromApiResponse($item);
            }
        }

        return $list;
    }

    /**
     * Parses the value with the given format, or returns null when it does not match or is not a valid date.
     */
    private function parseDateTime(string $format, string $value): ?\DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat($format, $value);

        // An impossible date such as February 30 parses with only a warning, rolled over into March.
        if ($dateTime === false || \DateTimeImmutable::getLastErrors() !== false) {
            return null;
        }

        return $dateTime;
    }
}
