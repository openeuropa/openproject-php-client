<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A version in an OpenProject instance.
 */
final readonly class Version
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?Formattable $description,
        public ?string $startDate,
        public ?string $endDate,
        public ?string $status,
        public ?string $sharing,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public ?Link $self,
        public ?Link $definingProject,
        public ?Link $availableInProjects,
    ) {
    }

    /**
     * Creates an instance from a decoded API version response.
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
            $hal->requireString('name'),
            $hal->formattable('description'),
            $hal->getString('startDate'),
            $hal->getString('endDate'),
            $hal->getString('status'),
            $hal->getString('sharing'),
            $hal->getDateTime('createdAt'),
            $hal->getDateTime('updatedAt'),
            $hal->link('self'),
            $hal->link('definingProject'),
            $hal->link('availableInProjects'),
        );
    }
}
