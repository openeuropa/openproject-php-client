<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * Basic information about an OpenProject instance, from the API root.
 */
final readonly class InstanceInfo
{
    /**
     * @internal
     */
    public function __construct(
        public string $instanceName,
        // Only present for tokens with admin privileges.
        public ?string $coreVersion,
    ) {
    }

    /**
     * Creates an instance from a decoded API root (GET /api/v3) response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self($hal->requireString('instanceName'), $hal->getString('coreVersion'));
    }
}
