<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;

/**
 * An attachment in an OpenProject instance.
 */
final readonly class Attachment
{
    /**
     * @internal
     */
    public function __construct(
        public int $id,
        public string $fileName,
        public ?int $fileSize,
        public Formattable $description,
        public string $status,
        public string $contentType,
        public AttachmentDigest $digest,
        public string $createdAt,
        public ?Link $self,
        public ?Link $container,
        public ?Link $author,
        public ?Link $downloadLocation,
    ) {
    }

    /**
     * Creates an instance from a decoded API attachment response.
     *
     * @param array<string, mixed> $data
     *   The decoded response body.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException
     */
    public static function fromApiResponse(array $data): self
    {
        $hal = new HalResource($data);

        $descriptionData = $data['description'] ?? null;
        if (!is_array($descriptionData)) {
            throw new UnexpectedResponseException('Expected required "description" object in API response.');
        }
        /** @var array<string, mixed> $descriptionData */

        $digestData = $data['digest'] ?? null;
        if (!is_array($digestData)) {
            throw new UnexpectedResponseException('Expected required "digest" object in API response.');
        }
        /** @var array<string, mixed> $digestData */

        return new self(
            $hal->requireInt('id'),
            $hal->requireString('fileName'),
            $hal->getInt('fileSize'),
            Formattable::fromApiResponse($descriptionData),
            $hal->requireString('status'),
            $hal->requireString('contentType'),
            AttachmentDigest::fromApiResponse($digestData),
            $hal->requireString('createdAt'),
            $hal->link('self'),
            $hal->link('container'),
            $hal->link('author'),
            $hal->link('downloadLocation'),
        );
    }
}
