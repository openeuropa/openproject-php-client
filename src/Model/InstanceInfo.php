<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Exception\OpenProjectException;

/**
 * Basic information about an OpenProject instance, from the API root.
 */
final readonly class InstanceInfo
{
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
     */
    public static function fromApiResponse(array $data): self
    {
        $instanceName = $data['instanceName'] ?? null;
        if (!is_string($instanceName)) {
            throw new OpenProjectException(
                'The API root response is missing the required "instanceName" property.',
            );
        }
        $coreVersion = $data['coreVersion'] ?? null;

        return new self($instanceName, is_string($coreVersion) ? $coreVersion : null);
    }
}
