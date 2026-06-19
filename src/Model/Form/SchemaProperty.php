<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model\Form;

use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Link;

/**
 * A single property entry from a form schema, describing writability and allowed values.
 */
final readonly class SchemaProperty
{
    /**
     * Class constructor.
     *
     * The spec marks hasDefault as required, but OpenProject omits it on some
     * properties (for example "subject"), so it is read leniently and is null
     * when absent.
     *
     * @param list<Link> $allowedValues
     *   The list of allowed value links for this property, empty when unconstrained.
     */
    public function __construct(
        public string $type,
        public string $name,
        public bool $required,
        public ?bool $hasDefault,
        public bool $writable,
        public ?string $location,
        public ?string $placeholder,
        public array $allowedValues,
    ) {
    }

    /**
     * Creates an instance from a decoded API schema property object.
     *
     * @param array<string, mixed> $data
     *   The decoded schema property object.
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        $links = $data['_links'] ?? null;
        $allowedValues = [];
        if (is_array($links)) {
            $raw = $links['allowedValues'] ?? null;
            if (is_array($raw) && $raw !== []) {
                foreach ($raw as $item) {
                    if (is_array($item)) {
                        /** @var array<string, mixed> $item */
                        $allowedValues[] = Link::fromApiResponse($item);
                    }
                }
            }
        }

        return new self(
            $hal->requireString('type'),
            $hal->requireString('name'),
            $hal->requireBool('required'),
            $hal->getBool('hasDefault'),
            $hal->requireBool('writable'),
            $hal->getString('location'),
            $hal->getString('placeholder'),
            $allowedValues,
        );
    }
}
