<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A work package in an OpenProject instance.
 */
final readonly class WorkPackage
{
    public function __construct(
        public ?int $id,
        public ?int $lockVersion,
        public string $subject,
        public ?string $startDate,
        public ?string $dueDate,
        public ?string $date,
        public ?string $estimatedTime,
        public ?string $derivedEstimatedTime,
        public ?string $spentTime,
        public ?int $percentageDone,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?bool $scheduleManually,
        public ?bool $readonly,
        public ?Formattable $description,
        public ?Link $status,
        public ?Link $type,
        public ?Link $priority,
        public ?Link $project,
        public ?Link $author,
        public ?Link $assignee,
    ) {
    }

    /**
     * Creates an instance from a decoded API work package response.
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

        return new self(
            $hal->getInt('id'),
            $hal->getInt('lockVersion'),
            $hal->requireString('subject'),
            $hal->getString('startDate'),
            $hal->getString('dueDate'),
            $hal->getString('date'),
            $hal->getString('estimatedTime'),
            $hal->getString('derivedEstimatedTime'),
            $hal->getString('spentTime'),
            $hal->getInt('percentageDone'),
            $hal->getString('createdAt'),
            $hal->getString('updatedAt'),
            $hal->getBool('scheduleManually'),
            $hal->getBool('readonly'),
            $description,
            $hal->link('status'),
            $hal->link('type'),
            $hal->link('priority'),
            $hal->link('project'),
            $hal->link('author'),
            $hal->link('assignee'),
        );
    }
}
