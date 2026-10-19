<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Attachment;
use Psr\Http\Message\StreamInterface;

/**
 * Endpoint for reading, uploading, and deleting attachments via the OpenProject API.
 */
final class AttachmentsEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single attachment by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Attachment
    {
        return Attachment::fromApiResponse($this->transport->get('attachments/' . $id));
    }

    /**
     * Returns the file content of an attachment.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function download(int $id): StreamInterface
    {
        return $this->transport->getRaw('attachments/' . $id . '/content');
    }

    /**
     * Uploads a file as a containerless attachment and returns the created resource.
     *
     * @param StreamInterface $contents
     *   The file contents, read from the start when the stream is seekable.
     *   Must be readable.
     * @param string $fileName
     *   The name to store the file under.
     * @param string $contentType
     *   The MIME type of the file.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function upload(
        StreamInterface $contents,
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
     * @param StreamInterface $contents
     *   The file contents, read from the start when the stream is seekable.
     *   Must be readable.
     * @param string $fileName
     *   The name to store the file under.
     * @param string $contentType
     *   The MIME type of the file.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function uploadToWorkPackage(
        int $workPackageId,
        StreamInterface $contents,
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
     * Deletes an attachment by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('attachments/' . $id);
    }

    /**
     * Builds the metadata and file parts of an attachment upload body.
     *
     * @return list<array{name: string, contents: string|StreamInterface, contentType: string, filename?: string}>
     */
    private function buildUploadParts(
        StreamInterface $contents,
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
