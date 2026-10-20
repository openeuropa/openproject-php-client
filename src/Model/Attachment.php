<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

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
        public ?Formattable $description,
        public ?string $status,
        public ?string $contentType,
        public ?AttachmentDigest $digest,
        public ?\DateTimeImmutable $createdAt,
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

        $digestData = $data['digest'] ?? null;
        $digest = null;
        if (
            is_array($digestData)
            && is_string($digestData['algorithm'] ?? null)
            && is_string($digestData['hash'] ?? null)
        ) {
            /** @var array<string, mixed> $digestData */
            $digest = AttachmentDigest::fromApiResponse($digestData);
        }

        return new self(
            $hal->requireInt('id'),
            $hal->requireString('fileName'),
            $hal->getInt('fileSize'),
            $hal->formattable('description'),
            $hal->getString('status'),
            $hal->getString('contentType'),
            $digest,
            $hal->getDateTime('createdAt'),
            $hal->link('self'),
            $hal->link('container'),
            $hal->link('author'),
            $hal->link('downloadLocation'),
        );
    }
}
