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
        $parts = $this->buildUploadParts($contents, $fileName, $contentType, $description);

        return Attachment::fromApiResponse($this->transport->postMultipart('attachments', $parts));
    }

    /**
     * Uploads a file as an attachment to a work package and returns the created resource.
     *
     * @param int $workPackageId
     *   ID of the work package to attach the file to.
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
    public function uploadToWorkPackage(
        int $workPackageId,
        string $contents,
        string $fileName,
        string $contentType,
        ?string $description = null,
    ): Attachment {
        $parts = $this->buildUploadParts($contents, $fileName, $contentType, $description);

        return Attachment::fromApiResponse(
            $this->transport->postMultipart('work_packages/' . $workPackageId . '/attachments', $parts),
        );
    }

    /**
     * Builds the metadata and file parts of an attachment upload body.
     *
     * @return list<array{name: string, contents: string, contentType: string, filename?: string}>
     */
    private function buildUploadParts(
        string $contents,
        string $fileName,
        string $contentType,
        ?string $description,
    ): array {
        // The API takes the stored file name from this part.
        $metadata = ['fileName' => $fileName];
        if ($description !== null) {
            $metadata['description'] = ['raw' => $description];
        }

        return [
            // The API requires the metadata part before the file part.
            [
                'name' => 'metadata',
                'contents' => json_encode($metadata, JSON_THROW_ON_ERROR),
                'contentType' => 'application/json',
            ],
            [
                'name' => 'file',
                'contents' => $contents,
                'contentType' => $contentType,
                // Required by the multipart format.
                'filename' => $fileName,
            ],
        ];
    }
}
