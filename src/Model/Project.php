<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A project in an OpenProject instance.
 */
final readonly class Project
{
    public function __construct(
        public ?int $id,
        public ?string $identifier,
        public ?string $name,
        public ?bool $active,
        public ?bool $public,
        public ?bool $favorited,
        public ?Formattable $description,
        public ?Formattable $statusExplanation,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    /**
     * Creates an instance from a decoded API project response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
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

        $statusExplanationData = $data['statusExplanation'] ?? null;
        $statusExplanation = null;
        if (
            is_array($statusExplanationData)
            && isset($statusExplanationData['format'])
            && is_string($statusExplanationData['format'])
        ) {
            /** @var array<string, mixed> $statusExplanationData */
            $statusExplanation = Formattable::fromApiResponse($statusExplanationData);
        }

        return new self(
            $hal->getInt('id'),
            $hal->getString('identifier'),
            $hal->getString('name'),
            $hal->getBool('active'),
            $hal->getBool('public'),
            $hal->getBool('favorited'),
            $description,
            $statusExplanation,
            $hal->getString('createdAt'),
            $hal->getString('updatedAt'),
        );
    }
}
