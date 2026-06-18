<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Project;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading projects from the OpenProject API.
 */
final class ProjectsEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single project by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Project
    {
        return Project::fromApiResponse($this->transport->get('projects/' . $id));
    }

    /**
     * Returns a generator that yields all projects, paginating automatically.
     *
     * @return \Generator<int, Project>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null, ?CollectionParams $params = null): \Generator
    {
        return $this->paginate('projects', Project::fromApiResponse(...), $filters, $params);
    }

    /**
     * Returns one page of projects as a PageResults object.
     *
     * @return PageResults<Project>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage('projects', Project::fromApiResponse(...), $filters, $params);
    }
}
