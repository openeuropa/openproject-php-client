<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\User;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading users from the OpenProject API.
 */
final class UsersEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single user by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): User
    {
        return User::fromApiResponse($this->transport->get('users/' . $id));
    }

    /**
     * Returns one page of users as a PageResults object.
     *
     * @return PageResults<User>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage('users', User::fromApiResponse(...), $filters, $params);
    }
}
