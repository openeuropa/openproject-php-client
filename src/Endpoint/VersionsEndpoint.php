<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Version;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading versions via the OpenProject API.
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
}
