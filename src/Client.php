<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient;

use OpenEuropa\OpenProjectClient\Authentication\AuthenticationInterface;
use OpenEuropa\OpenProjectClient\Endpoint\ActivitiesEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\AttachmentsEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ConfigurationEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\PrioritiesEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\ProjectsEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\RelationsEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\StatusesEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\TypesEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\UsersEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\VersionsEndpoint;
use OpenEuropa\OpenProjectClient\Endpoint\WorkPackagesEndpoint;
use OpenEuropa\OpenProjectClient\Http\Transport;
use OpenEuropa\OpenProjectClient\Model\InstanceInfo;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Entry point to the OpenProject API v3.
 *
 * Construct it with the instance base URL (subpath-hosted instances are
 * supported, e.g. "https://example.org/openproject" without "/api/v3"),
 * an authentication strategy, and explicit PSR-18/PSR-17 implementations:
 *
 * @code
 * $client = new Client(
 *     'https://example.org',
 *     new ApiKeyAuthentication($apiKey),
 *     new \GuzzleHttp\Client(),
 *     new \GuzzleHttp\Psr7\HttpFactory(),
 *     new \GuzzleHttp\Psr7\HttpFactory(),
 * );
 * @endcode
 */
final class Client
{
    private readonly Transport $transport;

    public function __construct(
        string $baseUrl,
        AuthenticationInterface $authentication,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $this->transport = new Transport(
            $baseUrl,
            $authentication,
            $httpClient,
            $requestFactory,
            $streamFactory,
        );
    }

    /**
     * Returns basic information about the OpenProject instance.
     *
     * Doubles as a connection and credentials test: it performs the smallest
     * possible authenticated call (GET /api/v3) and fails with the same
     * exceptions as any other API call.
     */
    public function info(): InstanceInfo
    {
        return InstanceInfo::fromApiResponse($this->transport->get());
    }

    /**
     * Returns the configuration endpoint for GET /api/v3/configuration.
     */
    public function configuration(): ConfigurationEndpoint
    {
        return new ConfigurationEndpoint($this->transport);
    }

    /**
     * Returns the statuses endpoint for GET /api/v3/statuses.
     */
    public function statuses(): StatusesEndpoint
    {
        return new StatusesEndpoint($this->transport);
    }

    /**
     * Returns the types endpoint for GET /api/v3/types.
     */
    public function types(): TypesEndpoint
    {
        return new TypesEndpoint($this->transport);
    }

    /**
     * Returns the priorities endpoint for GET /api/v3/priorities.
     */
    public function priorities(): PrioritiesEndpoint
    {
        return new PrioritiesEndpoint($this->transport);
    }

    /**
     * Returns the projects endpoint for GET /api/v3/projects.
     */
    public function projects(): ProjectsEndpoint
    {
        return new ProjectsEndpoint($this->transport);
    }

    /**
     * Returns the users endpoint for GET /api/v3/users.
     */
    public function users(): UsersEndpoint
    {
        return new UsersEndpoint($this->transport);
    }

    /**
     * Returns the work packages endpoint for GET /api/v3/work_packages.
     */
    public function workPackages(): WorkPackagesEndpoint
    {
        return new WorkPackagesEndpoint($this->transport);
    }

    /**
     * Returns the attachments endpoint for /api/v3/attachments.
     */
    public function attachments(): AttachmentsEndpoint
    {
        return new AttachmentsEndpoint($this->transport);
    }

    /**
     * Returns the activities endpoint for /api/v3/work_packages/{id}/activities.
     */
    public function activities(): ActivitiesEndpoint
    {
        return new ActivitiesEndpoint($this->transport);
    }

    /**
     * Returns the relations endpoint for /api/v3/relations.
     */
    public function relations(): RelationsEndpoint
    {
        return new RelationsEndpoint($this->transport);
    }

    /**
     * Returns the versions endpoint for /api/v3/versions.
     */
    public function versions(): VersionsEndpoint
    {
        return new VersionsEndpoint($this->transport);
    }
}
