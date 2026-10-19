<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A work package priority in an OpenProject instance.
 */
final readonly class Priority
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public ?string $name,
        public ?int $position,
        public ?bool $isDefault,
        public ?bool $isActive,
    ) {
    }

    /**
     * Creates an instance from a decoded API priority response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self(
            $hal->requireInt('id'),
            $hal->getString('name'),
            $hal->getInt('position'),
            $hal->getBool('isDefault'),
            $hal->getBool('isActive'),
        );
    }
}
