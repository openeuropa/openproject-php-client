<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Configuration;

/**
 * Endpoint for GET /api/v3/configuration.
 */
final class ConfigurationEndpoint extends AbstractEndpoint
{
    /**
     * Returns the global configuration of the OpenProject instance.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(): Configuration
    {
        return Configuration::fromApiResponse($this->transport->get('configuration'));
    }
}
