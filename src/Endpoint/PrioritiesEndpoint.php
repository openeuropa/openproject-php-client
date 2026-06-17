<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Priority;

/**
 * Endpoint for GET /api/v3/priorities and /api/v3/priorities/{id}.
 */
final class PrioritiesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single priority by its ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Priority
    {
        return Priority::fromApiResponse($this->transport->get('priorities/' . $id));
    }

    /**
     * Returns all priorities as a single decoded collection.
     *
     * @return PageResults<Priority>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(): PageResults
    {
        return $this->getCollection('priorities', Priority::fromApiResponse(...));
    }
}
