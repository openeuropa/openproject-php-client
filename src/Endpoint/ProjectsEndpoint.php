<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\Project;
use OpenEuropa\OpenProjectClient\Model\ProjectInput;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading and writing projects via the OpenProject API.
 */
final class ProjectsEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single project by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Project
    {
        return Project::fromApiResponse($this->transport->get('projects/' . $id));
    }

    /**
     * Returns a generator that yields all projects, paginating automatically.
     *
     * @return \Generator<int, Project>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null, ?CollectionParams $params = null): \Generator
    {
        return $this->paginate('projects', Project::fromApiResponse(...), $filters, $params);
    }

    /**
     * Returns one page of projects as a PageResults object.
     *
     * @return PageResults<Project>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage('projects', Project::fromApiResponse(...), $filters, $params);
    }

    /**
     * Returns the project create form, optionally pre-populated with a payload.
     *
     * @param ProjectInput|null $payload
     *   The project attributes to pre-populate and validate.
     *   Pass null (or omit) to request a blank form.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function createForm(?ProjectInput $payload = null): Form
    {
        $body = $payload?->toArray() ?? [];

        return Form::fromApiResponse($this->transport->post('projects/form', $body));
    }

    /**
     * Creates a project and returns the created resource.
     *
     * @param ProjectInput $payload
     *   The project attributes to create.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function create(ProjectInput $payload): Project
    {
        return Project::fromApiResponse($this->transport->post('projects', $payload->toArray()));
    }

    /**
     * Returns the project edit form for an existing project.
     *
     * @param int $id
     *   The ID of the project to validate the payload against.
     * @param ProjectInput $payload
     *   The project attributes to validate.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function updateForm(int $id, ProjectInput $payload): Form
    {
        return Form::fromApiResponse($this->transport->post('projects/' . $id . '/form', $payload->toArray()));
    }

    /**
     * Updates a project and returns the updated resource.
     *
     * @param ProjectInput $payload
     *   The project attributes to update.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function update(int $id, ProjectInput $payload): Project
    {
        return Project::fromApiResponse($this->transport->patch('projects/' . $id, $payload->toArray()));
    }

    /**
     * Deletes a project by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('projects/' . $id);
    }
}
