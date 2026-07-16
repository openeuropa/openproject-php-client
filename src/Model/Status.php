<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A work package status in an OpenProject instance.
 */
final readonly class Status
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?bool $isClosed,
        public ?string $color,
        public ?bool $isDefault,
        public ?bool $isReadonly,
        public ?bool $excludedFromTotals,
        public ?int $defaultDoneRatio,
        public ?int $position,
    ) {
    }

    /**
     * Creates an instance from a decoded API status response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self(
            $hal->requireInt('id'),
            $hal->getString('name'),
            $hal->getBool('isClosed'),
            $hal->getString('color'),
            $hal->getBool('isDefault'),
            $hal->getBool('isReadonly'),
            $hal->getBool('excludedFromTotals'),
            $hal->getInt('defaultDoneRatio'),
            $hal->getInt('position'),
        );
    }
}
