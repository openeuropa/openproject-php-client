<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A relation between two work packages in an OpenProject instance.
 */
final readonly class Relation
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public ?string $name,
        public ?string $type,
        public ?string $reverseType,
        public ?string $description,
        public ?int $lag,
        public ?Link $self,
        public ?Link $from,
        public ?Link $to,
    ) {
    }

    /**
     * Creates an instance from a decoded API relation response.
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
            $hal->getString('type'),
            $hal->getString('reverseType'),
            $hal->getString('description'),
            $hal->getInt('lag'),
            $hal->link('self'),
            $hal->link('from'),
            $hal->link('to'),
        );
    }
}
