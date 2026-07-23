<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\TimeEntry;
use OpenEuropa\OpenProjectClient\Model\TimeEntryInput;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading and writing time entries via the OpenProject API.
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

    /**
     * Returns the time entry create form, optionally pre-populated with a payload.
     *
     * @param TimeEntryInput|null $payload
     *   The time entry attributes to pre-populate and validate.
     *   Pass null (or omit) to request a blank form.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function createForm(?TimeEntryInput $payload = null): Form
    {
        $body = $payload?->toArray() ?? [];

        return Form::fromApiResponse($this->transport->post('time_entries/form', $body));
    }

    /**
     * Creates a time entry and returns the created resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function create(TimeEntryInput $payload): TimeEntry
    {
        return TimeEntry::fromApiResponse($this->transport->post('time_entries', $payload->toArray()));
    }

    /**
     * Returns the time entry edit form for an existing time entry.
     *
     * @param int $id
     *   The ID of the time entry to validate the payload against.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function updateForm(int $id, TimeEntryInput $payload): Form
    {
        return Form::fromApiResponse($this->transport->post('time_entries/' . $id . '/form', $payload->toArray()));
    }

    /**
     * Updates a time entry and returns the updated resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function update(int $id, TimeEntryInput $payload): TimeEntry
    {
        return TimeEntry::fromApiResponse($this->transport->patch('time_entries/' . $id, $payload->toArray()));
    }

    /**
     * Deletes a time entry by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('time_entries/' . $id);
    }
}
