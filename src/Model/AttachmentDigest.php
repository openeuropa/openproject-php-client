<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * The checksum of an attachment's content.
 */
final readonly class AttachmentDigest
{
    /**
     * @internal
     */
    public function __construct(
        public string $algorithm,
        public string $hash,
    ) {
    }

    /**
     * Creates an instance from a decoded digest object.
     *
     * @param array<string, mixed> $data
     *   The decoded digest object.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        return new self(
            $hal->requireString('algorithm'),
            $hal->requireString('hash'),
        );
    }
}
