<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Relation;
use OpenEuropa\OpenProjectClient\Model\RelationInput;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading and writing relations via the OpenProject API.
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

    /**
     * Creates a relation on a work package and returns the created resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function create(int $workPackageId, RelationInput $payload): Relation
    {
        return Relation::fromApiResponse(
            $this->transport->post('work_packages/' . $workPackageId . '/relations', $payload->toArray()),
        );
    }

    /**
     * Updates a relation and returns the updated resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function update(int $id, RelationInput $payload): Relation
    {
        return Relation::fromApiResponse($this->transport->patch('relations/' . $id, $payload->toArray()));
    }

    /**
     * Deletes a relation by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('relations/' . $id);
    }
}
