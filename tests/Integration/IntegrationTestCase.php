<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Integration;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use OpenEuropa\OpenProjectClient\Authentication\ApiKeyAuthentication;
use OpenEuropa\OpenProjectClient\Client;
use OpenEuropa\OpenProjectClient\Exception\NotFoundException;
use OpenEuropa\OpenProjectClient\Model\Project;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Model\WorkPackageInput;
use OpenEuropa\OpenProjectClient\Query\Filter;
use OpenEuropa\OpenProjectClient\Query\FilterOperator;
use OpenEuropa\OpenProjectClient\Query\FilterSet;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Base for tests that drive the client against a real OpenProject instance.
 *
 * Supports the following environment variables:
 *   - OPENPROJECT_API_TOKEN: required. The API token to authenticate with.0
 *   - OPENPROJECT_BASE_URL: optional. Instance base URL; defaults to
 *     http://openproject (the Compose service name).
 *   - OPENPROJECT_HOST_HEADER: optional. Host header to send; defaults to
 *     localhost:8080. Set empty to leave the Host untouched.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected Client $client;

    /**
     * IDs of work packages created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    protected array $createdWorkPackageIds = [];

    /**
     * IDs of projects created during a test run, deleted in tearDown.
     *
     * @var list<int>
     */
    protected array $createdProjectIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $token = getenv('OPENPROJECT_API_TOKEN');
        if (!is_string($token) || $token === '') {
            self::markTestSkipped('Integration tests require OPENPROJECT_API_TOKEN.');
        }

        // Determine the base url where the OpenProject API can be reached.
        $baseUrl = getenv('OPENPROJECT_BASE_URL');
        if (!is_string($baseUrl) || $baseUrl === '') {
            $baseUrl = 'http://openproject';
        }

        $this->client = new Client(
            $baseUrl,
            new ApiKeyAuthentication($token),
            $this->createHttpClient(),
            new HttpFactory(),
            new HttpFactory(),
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->createdWorkPackageIds as $id) {
            try {
                $this->client->workPackages()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
            }
        }

        foreach ($this->createdProjectIds as $id) {
            try {
                $this->client->projects()->delete($id);
            } catch (NotFoundException) {
                // Already deleted; nothing to clean up.
                continue;
            }

            $this->waitForProjectRemoval($id);
        }
    }

    /**
     * Returns the ID of the first active project on the instance.
     *
     * Deleting a project archives it (active becomes false) and removes it
     * asynchronously, and the API rejects type and work-package lookups on an
     * archived project, so the suite always works against an active one.
     */
    protected function firstActiveProjectId(): int
    {
        $projects = $this->client->projects()->listPage(
            new FilterSet(new Filter('active', FilterOperator::EQUAL, ['t'])),
        );
        self::assertNotEmpty($projects->elements, 'The instance must have at least one active project.');

        return $projects->elements[0]->id;
    }

    /**
     * Creates a project and registers it for teardown.
     */
    protected function createProject(): Project
    {
        $suffix = uniqid();

        $project = $this->client->projects()->create(
            (new ProjectInput())->name('phpclient project ' . $suffix)->identifier('phpclient-' . $suffix),
        );

        $this->createdProjectIds[] = $project->id;

        return $project;
    }

    /**
     * Creates a work package in the first active project and registers it for teardown.
     */
    protected function createWorkPackage(): WorkPackage
    {
        $projectId = $this->firstActiveProjectId();

        $types = $this->client->types()->allByProject($projectId);
        self::assertNotEmpty($types->elements, 'The project must have at least one type.');
        $typeId = $types->elements[0]->id;

        $wp = $this->client->workPackages()->create(
            (new WorkPackageInput())->subject('phpclient-' . uniqid())->project($projectId)->type($typeId),
        );

        $this->createdWorkPackageIds[] = $wp->id;

        return $wp;
    }

    /**
     * Waits for a deleted project's background removal to complete.
     *
     * A project delete archives the project at once and removes its row
     * through a background job, so the row can still exist after the request
     * returns. Waiting for it to disappear keeps that removal from
     * overlapping the next project the suite creates: two of those running at
     * once deadlock on the projects table in Postgres. Giving up silently
     * once the deadline passes is deliberate, because teardown cleanup must
     * never fail an otherwise passing test.
     */
    private function waitForProjectRemoval(int $id): void
    {
        $deadline = microtime(true) + 10.0;

        while (microtime(true) < $deadline) {
            try {
                $this->client->projects()->get($id);
            } catch (NotFoundException) {
                return;
            }

            usleep(100_000);
        }
    }

    /**
     * Builds a PSR-18 HTTP client for integration tests.
     */
    private function createHttpClient(): ClientInterface
    {
        $config = [];

        // Once seeded, OpenProject supports only the domain it has been seeded from.
        // If another domain is used for seeding, it can be specified with this env variable.
        $hostHeader = getenv('OPENPROJECT_HOST_HEADER');
        if ($hostHeader === false) {
            $hostHeader = 'localhost:8080';
        }
        if ($hostHeader !== '') {
            $stack = HandlerStack::create();
            $stack->push(Middleware::mapRequest(
                static fn (RequestInterface $request): RequestInterface =>
                    $request->withHeader('Host', $hostHeader),
            ));
            $config['handler'] = $stack;
        }

        return new GuzzleClient($config);
    }
}
