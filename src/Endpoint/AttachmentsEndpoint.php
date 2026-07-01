<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Attachment;

/**
 * Endpoint for uploading and reading attachments via the OpenProject API.
 */
final class AttachmentsEndpoint extends AbstractEndpoint
{
    /**
     * Uploads a file as a containerless attachment and returns the created resource.
     *
     * @param string $contents
     *   The raw file bytes.
     * @param string $fileName
     *   The name to store the file under.
     * @param string $contentType
     *   The MIME type of the file.
     * @param string|null $description
     *   An optional description of the file.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function upload(
        string $contents,
        string $fileName,
        string $contentType,
        ?string $description = null,
    ): Attachment {
        $metadata = ['fileName' => $fileName];
        if ($description !== null) {
            $metadata['description'] = ['raw' => $description];
        }

        $parts = [
            [
                'name' => 'metadata',
                'contents' => json_encode($metadata, JSON_THROW_ON_ERROR),
                'contentType' => 'application/json',
            ],
            [
                'name' => 'file',
                'contents' => $contents,
                'contentType' => $contentType,
                'filename' => $fileName,
            ],
        ];

        return Attachment::fromApiResponse($this->transport->postMultipart('attachments', $parts));
    }
}
