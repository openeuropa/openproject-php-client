<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Version;
use OpenEuropa\OpenProjectClient\Model\VersionInput;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading and writing versions via the OpenProject API.
 */
final class VersionsEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single version by its ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Version
    {
        return Version::fromApiResponse($this->transport->get('versions/' . $id));
    }

    /**
     * Returns all versions matching the given filters as a single decoded collection.
     *
     * @return PageResults<Version>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null): PageResults
    {
        return $this->getCollectionPage('versions', Version::fromApiResponse(...), $filters);
    }

    /**
     * Returns all versions available in a project as a single decoded collection.
     *
     * @return PageResults<Version>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function allByProject(int $projectId): PageResults
    {
        return $this->getCollection('projects/' . $projectId . '/versions', Version::fromApiResponse(...));
    }

    /**
     * Returns the version create form, optionally pre-populated with a payload.
     *
     * @param VersionInput|null $payload
     *   The version attributes to pre-populate and validate.
     *   Pass null (or omit) to request a blank form.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function createForm(?VersionInput $payload = null): Form
    {
        $body = $payload?->toArray() ?? [];

        return Form::fromApiResponse($this->transport->post('versions/form', $body));
    }

    /**
     * Creates a version and returns the created resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function create(VersionInput $payload): Version
    {
        return Version::fromApiResponse($this->transport->post('versions', $payload->toArray()));
    }

    /**
     * Returns the version edit form for an existing version.
     *
     * @param int $id
     *   The ID of the version to validate the payload against.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function updateForm(int $id, VersionInput $payload): Form
    {
        return Form::fromApiResponse($this->transport->post('versions/' . $id . '/form', $payload->toArray()));
    }

    /**
     * Updates a version and returns the updated resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function update(int $id, VersionInput $payload): Version
    {
        return Version::fromApiResponse($this->transport->patch('versions/' . $id, $payload->toArray()));
    }

    /**
     * Deletes a version by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('versions/' . $id);
    }
}
