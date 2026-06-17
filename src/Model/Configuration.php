<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * Global configuration of an OpenProject instance.
 */
final readonly class Configuration
{
    /**
     * @param list<int> $perPageOptions
     *   The selectable page-size options for collection endpoints.
     * @param list<string> $activeFeatureFlags
     *   The feature flags currently enabled on the instance.
     */
    public function __construct(
        public ?int $maximumAttachmentFileSize,
        public ?string $hostName,
        public array $perPageOptions,
        public ?string $durationFormat,
        public array $activeFeatureFlags,
    ) {
    }

    /**
     * Creates an instance from a decoded API configuration response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        $perPageOptions = array_values(array_filter($hal->getArray('perPageOptions'), 'is_int'));
        $activeFeatureFlags = array_values(array_filter($hal->getArray('activeFeatureFlags'), 'is_string'));

        return new self(
            $hal->getInt('maximumAttachmentFileSize'),
            $hal->getString('hostName'),
            $perPageOptions,
            $hal->getString('durationFormat'),
            $activeFeatureFlags,
        );
    }
}
