<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Type;

/**
 * Endpoint for GET /api/v3/types and /api/v3/types/{id}.
 */
final class TypesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single type by its ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Type
    {
        return Type::fromApiResponse($this->transport->get('types/' . $id));
    }

    /**
     * Returns all types as a single decoded collection.
     *
     * @return PageResults<Type>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(): PageResults
    {
        return $this->getCollection('types', Type::fromApiResponse(...));
    }

    /**
     * Returns all types available in a project as a single decoded collection.
     *
     * @return PageResults<Type>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function allByProject(int $projectId): PageResults
    {
        return $this->getCollection('projects/' . $projectId . '/types', Type::fromApiResponse(...));
    }
}
