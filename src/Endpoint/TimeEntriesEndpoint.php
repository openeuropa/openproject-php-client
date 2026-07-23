<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\TimeEntry;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading time entries from the OpenProject API.
 */
final class TimeEntriesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single time entry by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): TimeEntry
    {
        return TimeEntry::fromApiResponse($this->transport->get('time_entries/' . $id));
    }

    /**
     * Returns a generator that yields all time entries, paginating automatically.
     *
     * @return \Generator<int, TimeEntry>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null, ?CollectionParams $params = null): \Generator
    {
        return $this->paginate('time_entries', TimeEntry::fromApiResponse(...), $filters, $params);
    }

    /**
     * Returns one page of time entries as a PageResults object.
     *
     * @return PageResults<TimeEntry>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage('time_entries', TimeEntry::fromApiResponse(...), $filters, $params);
    }
}
