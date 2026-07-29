<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\Membership;
use OpenEuropa\OpenProjectClient\Model\MembershipInput;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading and writing memberships via the OpenProject API.
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

    /**
     * Returns the membership create form, optionally pre-populated with a payload.
     *
     * @param MembershipInput|null $payload
     *   The membership attributes to pre-populate and validate.
     *   Pass null (or omit) to request a blank form.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function createForm(?MembershipInput $payload = null): Form
    {
        $body = $payload?->toArray() ?? [];

        return Form::fromApiResponse($this->transport->post('memberships/form', $body));
    }

    /**
     * Creates a membership and returns the created resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function create(MembershipInput $payload): Membership
    {
        return Membership::fromApiResponse($this->transport->post('memberships', $payload->toArray()));
    }

    /**
     * Returns the membership edit form for an existing membership.
     *
     * @param int $id
     *   The ID of the membership to validate the payload against.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function updateForm(int $id, MembershipInput $payload): Form
    {
        return Form::fromApiResponse($this->transport->post('memberships/' . $id . '/form', $payload->toArray()));
    }

    /**
     * Updates a membership and returns the updated resource.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function update(int $id, MembershipInput $payload): Membership
    {
        return Membership::fromApiResponse($this->transport->patch('memberships/' . $id, $payload->toArray()));
    }

    /**
     * Deletes a membership by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('memberships/' . $id);
    }
}
