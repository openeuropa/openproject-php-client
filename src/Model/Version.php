<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
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
        public Formattable $description,
        public ?string $startDate,
        public ?string $endDate,
        public string $status,
        public string $sharing,
        public string $createdAt,
        public string $updatedAt,
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
        if (!is_array($descriptionData)) {
            throw new UnexpectedResponseException('Expected required "description" object in API response.');
        }
        /** @var array<string, mixed> $descriptionData */

        return new self(
            $hal->requireInt('id'),
            $hal->requireString('name'),
            Formattable::fromApiResponse($descriptionData),
            $hal->getString('startDate'),
            $hal->getString('endDate'),
            $hal->requireString('status'),
            $hal->requireString('sharing'),
            $hal->requireString('createdAt'),
            $hal->requireString('updatedAt'),
            $hal->link('self'),
            $hal->link('definingProject'),
            $hal->link('availableInProjects'),
        );
    }
}
