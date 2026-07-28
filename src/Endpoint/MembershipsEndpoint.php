<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Membership;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading memberships via the OpenProject API.
 */
final class MembershipsEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single membership by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Membership
    {
        return Membership::fromApiResponse($this->transport->get('memberships/' . $id));
    }

    /**
     * Returns a generator that yields all memberships, paginating automatically.
     *
     * @return \Generator<int, Membership>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null, ?CollectionParams $params = null): \Generator
    {
        return $this->paginate('memberships', Membership::fromApiResponse(...), $filters, $params);
    }

    /**
     * Returns one page of memberships as a PageResults object.
     *
     * @return PageResults<Membership>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage('memberships', Membership::fromApiResponse(...), $filters, $params);
    }
}
