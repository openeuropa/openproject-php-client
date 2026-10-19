<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A work package type in an OpenProject instance.
 */
final readonly class Type
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $color,
        public int $position,
        public bool $isDefault,
        public bool $isMilestone,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /**
     * Creates an instance from a decoded API type response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self(
            $hal->requireInt('id'),
            $hal->requireString('name'),
            $hal->requireString('color'),
            $hal->requireInt('position'),
            $hal->requireBool('isDefault'),
            $hal->requireBool('isMilestone'),
            $hal->requireString('createdAt'),
            $hal->requireString('updatedAt'),
        );
    }
}
