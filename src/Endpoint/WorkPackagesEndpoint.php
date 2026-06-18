<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading work packages from the OpenProject API.
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
     * Returns one page of work packages as a PageResults object.
     *
     * When no filters are given, an empty FilterSet is sent explicitly so the
     * server returns all work packages instead of applying its open-only default.
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
}
