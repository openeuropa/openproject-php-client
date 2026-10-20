<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A project in an OpenProject instance.
 */
final readonly class Project
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public ?string $identifier,
        public string $name,
        public ?bool $active,
        public ?bool $public,
        public ?bool $favorited,
        public ?Formattable $description,
        public ?Formattable $statusExplanation,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * Creates an instance from a decoded API project response.
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
            $hal->getString('identifier'),
            $hal->requireString('name'),
            $hal->getBool('active'),
            $hal->getBool('public'),
            $hal->getBool('favorited'),
            $hal->formattable('description'),
            $hal->formattable('statusExplanation'),
            $hal->getDateTime('createdAt'),
            $hal->getDateTime('updatedAt'),
        );
    }
}
