<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * A catch-all bag of custom field values from a HAL response.
 */
final readonly class CustomFields
{
    /**
     * @internal
     *
     * @param array<string, mixed> $values
     *   Raw top-level customField<N> values keyed by field name.
     * @param array<string, list<Link>> $links
     *   Reference-type customField<N> links, each normalized to a list of Link.
     */
    public function __construct(
        public array $values,
        public array $links,
    ) {
    }

    /**
     * Creates an instance from a decoded API response body.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     */
    public static function fromApiResponse(array $data): self
    {
        $values = [];
        foreach ($data as $key => $value) {
            if (self::isCustomFieldKey($key)) {
                $values[$key] = $value;
            }
        }

        /** @var array<string, list<Link>> $links */
        $links = [];
        $linksData = $data['_links'] ?? null;
        if (is_array($linksData)) {
            foreach ($linksData as $key => $entry) {
                if (!is_string($key) || !self::isCustomFieldKey($key) || !is_array($entry)) {
                    continue;
                }

                if (array_is_list($entry)) {
                    /** @var list<Link> $list */
                    $list = [];
                    foreach ($entry as $item) {
                        if (is_array($item)) {
                            /** @var array<string, mixed> $item */
                            $list[] = Link::fromApiResponse($item);
                        }
                    }
                    $links[$key] = $list;
                } else {
                    /** @var array<string, mixed> $entry */
                    $links[$key] = [Link::fromApiResponse($entry)];
                }
            }
        }

        return new self($values, $links);
    }

    private static function isCustomFieldKey(string $key): bool
    {
        return preg_match('/^customField\d+$/', $key) === 1;
    }
}
