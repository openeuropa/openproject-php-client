<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * OpenProject rich-text value with format, raw source, and server-rendered HTML.
 */
final readonly class Formattable
{
    /**
     * @internal
     *
     * @param TextFormat|null $format
     *   Null when the API sends a format the enum has no case for, such as
     *   one a plugin adds.
     */
    public function __construct(
        public ?TextFormat $format,
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
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self(
            TextFormat::tryFrom($hal->requireString('format')),
            $hal->getString('raw'),
            $hal->getString('html'),
        );
    }
}
