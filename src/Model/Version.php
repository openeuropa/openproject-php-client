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
        public ?string $createdAt,
        public ?string $updatedAt,
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

        $descriptionData = $data['description'] ?? null;
        $description = null;
        if (is_array($descriptionData) && isset($descriptionData['format']) && is_string($descriptionData['format'])) {
            /** @var array<string, mixed> $descriptionData */
            $description = Formattable::fromApiResponse($descriptionData);
        }

        return new self(
            $hal->requireInt('id'),
            $hal->requireString('name'),
            $description,
            $hal->getString('startDate'),
            $hal->getString('endDate'),
            $hal->getString('status'),
            $hal->getString('sharing'),
            $hal->getString('createdAt'),
            $hal->getString('updatedAt'),
            $hal->link('self'),
            $hal->link('definingProject'),
            $hal->link('availableInProjects'),
        );
    }
}
