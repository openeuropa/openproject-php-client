<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A category in an OpenProject instance.
 */
final readonly class Category
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?Link $self,
        public ?Link $project,
        public ?Link $defaultAssignee,
    ) {
    }

    /**
     * Creates an instance from a decoded API category response.
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
            $hal->link('self'),
            $hal->link('project'),
            $hal->link('defaultAssignee'),
        );
    }
}
