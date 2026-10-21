<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A work package in an OpenProject instance.
 */
final readonly class WorkPackage
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public ?int $lockVersion,
        public string $subject,
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $dueDate,
        public ?\DateTimeImmutable $date,
        public ?\DateInterval $estimatedTime,
        public ?\DateInterval $derivedEstimatedTime,
        public ?\DateInterval $spentTime,
        public ?int $percentageDone,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public ?bool $scheduleManually,
        public ?bool $readonly,
        public ?Formattable $description,
        public ?Link $status,
        public ?Link $type,
        public ?Link $priority,
        public ?Link $project,
        public ?Link $author,
        public ?Link $assignee,
        public CustomFields $customFields,
    ) {
    }

    /**
     * Creates an instance from a decoded API work package response.
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
            $hal->getInt('lockVersion'),
            $hal->requireString('subject'),
            $hal->getDate('startDate'),
            $hal->getDate('dueDate'),
            $hal->getDate('date'),
            $hal->getDuration('estimatedTime'),
            $hal->getDuration('derivedEstimatedTime'),
            $hal->getDuration('spentTime'),
            $hal->getInt('percentageDone'),
            $hal->getDateTime('createdAt'),
            $hal->getDateTime('updatedAt'),
            $hal->getBool('scheduleManually'),
            $hal->getBool('readonly'),
            $hal->formattable('description'),
            $hal->link('status'),
            $hal->link('type'),
            $hal->link('priority'),
            $hal->link('project'),
            $hal->link('author'),
            $hal->link('assignee'),
            CustomFields::fromApiResponse($data),
        );
    }
}
