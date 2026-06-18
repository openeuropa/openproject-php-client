<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading work packages from the OpenProject API.
 *
 * In OpenProject, the `work_packages` endpoint returns only open work packages by
 * default when no filters are passed. We purposely pass an empty FilterSet, which
 * forces the API to return all results.
 */
final class WorkPackagesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single work package by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): WorkPackage
    {
        return WorkPackage::fromApiResponse($this->transport->get('work_packages/' . $id));
    }

    /**
     * Returns a generator that yields all work packages, paginating automatically.
     *
     * @return \Generator<int, WorkPackage>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null, ?CollectionParams $params = null): \Generator
    {
        return $this->paginate(
            'work_packages',
            WorkPackage::fromApiResponse(...),
            $filters ?? new FilterSet(),
            $params,
        );
    }

    /**
     * Returns one page of work packages as a PageResults object.
     *
     * @return PageResults<WorkPackage>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage(
            'work_packages',
            WorkPackage::fromApiResponse(...),
            $filters ?? new FilterSet(),
            $params,
        );
    }

    /**
     * Returns a generator that yields all work packages in a project, paginating automatically.
     *
     * @return \Generator<int, WorkPackage>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listByProject(
        int $projectId,
        ?FilterSet $filters = null,
        ?CollectionParams $params = null,
    ): \Generator {
        return $this->paginate(
            'projects/' . $projectId . '/work_packages',
            WorkPackage::fromApiResponse(...),
            $filters ?? new FilterSet(),
            $params,
        );
    }
}
