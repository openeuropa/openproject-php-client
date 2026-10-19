<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A time entry in an OpenProject instance.
 */
final readonly class TimeEntry
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public ?Formattable $comment,
        public ?string $spentOn,
        public ?string $hours,
        public ?bool $ongoing,
        public ?string $startTime,
        public ?string $endTime,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?Link $self,
        public ?Link $project,
        public ?Link $entity,
        public ?Link $user,
        public ?Link $activity,
        public CustomFields $customFields,
    ) {
    }

    /**
     * Creates an instance from a decoded API time entry response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        $commentData = $data['comment'] ?? null;
        $comment = null;
        if (is_array($commentData) && isset($commentData['format']) && is_string($commentData['format'])) {
            /** @var array<string, mixed> $commentData */
            $comment = Formattable::fromApiResponse($commentData);
        }

        return new self(
            $hal->requireInt('id'),
            $comment,
            $hal->getString('spentOn'),
            $hal->getString('hours'),
            $hal->getBool('ongoing'),
            $hal->getString('startTime'),
            $hal->getString('endTime'),
            $hal->getString('createdAt'),
            $hal->getString('updatedAt'),
            $hal->link('self'),
            $hal->link('project'),
            $hal->link('entity'),
            $hal->link('user'),
            $hal->link('activity'),
            CustomFields::fromApiResponse($data),
        );
    }
}
