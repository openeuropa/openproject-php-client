<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * A single per-field validation error from an HTTP 422 response.
 */
final readonly class ValidationError
{
    /**
     * @internal
     */
    public function __construct(
        public ?string $attribute,
        public ?string $message,
        public ?string $identifier,
    ) {
    }

    /**
     * Creates an instance from a decoded API error element.
     *
     * @param array<string, mixed> $data
     *   A decoded error object from the API response body.
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        $embedded = $hal->getArray('_embedded');
        $details = isset($embedded['details']) && is_array($embedded['details']) ? $embedded['details'] : [];
        $attribute = isset($details['attribute']) && is_string($details['attribute']) ? $details['attribute'] : null;

        return new self(
            $attribute,
            $hal->getString('message'),
            $hal->getString('errorIdentifier'),
        );
    }
}
