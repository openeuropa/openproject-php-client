<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\User;

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
}
