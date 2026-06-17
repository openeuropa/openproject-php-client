<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * A HAL _links entry with href and optional title.
 */
final readonly class Link
{
    public function __construct(
        public ?string $href,
        public ?string $title,
    ) {
    }

    /**
     * Creates an instance from a decoded HAL link object.
     *
     * @param array<string, mixed> $data
     *   The decoded link object.
     */
    public static function fromApiResponse(array $data): self
    {
        $href = $data['href'] ?? null;
        $title = $data['title'] ?? null;

        return new self(
            is_string($href) ? $href : null,
            is_string($title) ? $title : null,
        );
    }

    /**
     * Extracts the last path segment of href and returns it as an int when
     * it is all digits; otherwise returns null.
     */
    public function id(): ?int
    {
        if ($this->href === null || $this->href === '') {
            return null;
        }

        $trimmed = rtrim($this->href, '/');
        $pos = strrpos($trimmed, '/');
        $seg = $pos !== false ? substr($trimmed, $pos + 1) : $trimmed;

        return $seg !== '' && ctype_digit($seg) ? (int) $seg : null;
    }
}
