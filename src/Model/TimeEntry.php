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
        public ?\DateTimeImmutable $spentOn,
        public ?string $hours,
        public ?bool $ongoing,
        public ?\DateTimeImmutable $startTime,
        public ?\DateTimeImmutable $endTime,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
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

        return new self(
            $hal->requireInt('id'),
            $hal->formattable('comment'),
            $hal->getDate('spentOn'),
            $hal->getString('hours'),
            $hal->getBool('ongoing'),
            $hal->getDateTime('startTime'),
            $hal->getDateTime('endTime'),
            $hal->getDateTime('createdAt'),
            $hal->getDateTime('updatedAt'),
            $hal->link('self'),
            $hal->link('project'),
            $hal->link('entity'),
            $hal->link('user'),
            $hal->link('activity'),
            CustomFields::fromApiResponse($data),
        );
    }
}
