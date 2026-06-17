<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Status;

/**
 * Endpoint for GET /api/v3/statuses and /api/v3/statuses/{id}.
 */
final class StatusesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single status by its ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Status
    {
        return Status::fromApiResponse($this->transport->get('statuses/' . $id));
    }

    /**
     * Returns all statuses as a single decoded collection.
     *
     * @return PageResults<Status>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(): PageResults
    {
        return $this->getCollection('statuses', Status::fromApiResponse(...));
    }
}
