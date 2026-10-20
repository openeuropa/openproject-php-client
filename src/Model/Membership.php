<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A membership in an OpenProject instance.
 */
final readonly class Membership
{
    /**
     * @internal
     *
     * @param list<Link> $roles
     */
    public function __construct(
        public int $id,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public ?Link $self,
        public ?Link $project,
        public ?Link $principal,
        public array $roles,
    ) {
    }

    /**
     * Creates an instance from a decoded API membership response.
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
            $hal->getDateTime('createdAt'),
            $hal->getDateTime('updatedAt'),
            $hal->link('self'),
            $hal->link('project'),
            $hal->link('principal'),
            $hal->linkList('roles'),
        );
    }
}
