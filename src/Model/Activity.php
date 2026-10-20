<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A journal activity on an OpenProject work package, including comments.
 */
final readonly class Activity
{
    /**
     * @internal
     *
     * @param list<Formattable> $details
     *   The changed-attribute descriptions recorded in this activity. Empty
     *   when the API omits the field.
     */
    public function __construct(
        public int $id,
        public ?int $version,
        public ?Formattable $comment,
        public array $details,
        public ?bool $internal,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?Link $self,
        public ?Link $user,
        public ?Link $workPackage,
    ) {
    }

    /**
     * Creates an instance from a decoded API activity response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        $detailsData = $data['details'] ?? null;
        $details = [];
        if (is_array($detailsData)) {
            foreach ($detailsData as $detail) {
                if (is_array($detail)) {
                    /** @var array<string, mixed> $detail */
                    $details[] = Formattable::fromApiResponse($detail);
                }
            }
        }

        return new self(
            $hal->requireInt('id'),
            $hal->getInt('version'),
            $hal->formattable('comment'),
            $details,
            $hal->getBool('internal'),
            $hal->getString('createdAt'),
            $hal->getString('updatedAt'),
            $hal->link('self'),
            $hal->link('user'),
            $hal->link('workPackage'),
        );
    }
}
