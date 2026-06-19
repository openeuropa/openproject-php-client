<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Query\CollectionParams;
use OpenEuropa\OpenProjectClient\Query\FilterSet;

/**
 * Endpoint for reading and writing work packages via the OpenProject API.
 *
 * In OpenProject, the `work_packages` endpoint returns only open work packages by
 * default when no filters are passed. We purposely pass an empty FilterSet, which
 * forces the API to return all results.
 */
final class WorkPackagesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single work package by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): WorkPackage
    {
        return WorkPackage::fromApiResponse($this->transport->get('work_packages/' . $id));
    }

    /**
     * Returns a generator that yields all work packages, paginating automatically.
     *
     * @return \Generator<int, WorkPackage>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function all(?FilterSet $filters = null, ?CollectionParams $params = null): \Generator
    {
        return $this->paginate(
            'work_packages',
            WorkPackage::fromApiResponse(...),
            $filters ?? new FilterSet(),
            $params,
        );
    }

    /**
     * Returns one page of work packages as a PageResults object.
     *
     * @return PageResults<WorkPackage>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listPage(?FilterSet $filters = null, ?CollectionParams $params = null): PageResults
    {
        return $this->getCollectionPage(
            'work_packages',
            WorkPackage::fromApiResponse(...),
            $filters ?? new FilterSet(),
            $params,
        );
    }

    /**
     * Returns a generator that yields all work packages in a project, paginating automatically.
     *
     * @return \Generator<int, WorkPackage>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function listByProject(
        int $projectId,
        ?FilterSet $filters = null,
        ?CollectionParams $params = null,
    ): \Generator {
        return $this->paginate(
            'projects/' . $projectId . '/work_packages',
            WorkPackage::fromApiResponse(...),
            $filters ?? new FilterSet(),
            $params,
        );
    }

    /**
     * Returns the work-package create form, optionally scoped to a project.
     *
     * The API returns HTTP 200 even when the payload has validation errors;
     * check the returned Form's validationErrors for field-level problems.
     *
     * @param array<string, mixed> $payload
     *   The work-package attributes to pre-populate and validate.
     * @param int|null $projectId
     *   When provided, the form is scoped to this project.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function createForm(array $payload = [], ?int $projectId = null): Form
    {
        $path = $projectId !== null
            ? 'projects/' . $projectId . '/work_packages/form'
            : 'work_packages/form';

        return Form::fromApiResponse($this->transport->post($path, $payload));
    }
}
