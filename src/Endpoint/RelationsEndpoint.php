<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Relation;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for GET /api/v3/relations and /api/v3/relations/{id}.
 */
final class RelationsEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single relation by its ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Relation
    {
        return Relation::fromApiResponse($this->transport->get('relations/' . $id));
    }

    /**
     * Returns all relations matching the given filters as a single decoded collection.
     *
     * @return PageResults<Relation>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null): PageResults
    {
        return $this->getCollectionPage('relations', Relation::fromApiResponse(...), $filters);
    }
}
