<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;

/**
 * OpenProject rich-text value with format, raw source, and server-rendered HTML.
 */
final readonly class Formattable
{
    public function __construct(
        public string $format,
        public ?string $raw,
        public ?string $html,
    ) {
    }

    /**
     * Creates an instance from a decoded Formattable object.
     *
     * @param array<string, mixed> $data
     *   The decoded formattable object.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     *   When the required "format" property is missing or not a string.
     */
    public static function fromApiResponse(array $data): self
    {
        $format = $data['format'] ?? null;
        if (!is_string($format)) {
            throw new OpenProjectException(
                'The Formattable object is missing the required "format" property.',
            );
        }

        $raw = $data['raw'] ?? null;
        $html = $data['html'] ?? null;

        return new self(
            $format,
            is_string($raw) ? $raw : null,
            is_string($html) ? $html : null,
        );
    }
}
