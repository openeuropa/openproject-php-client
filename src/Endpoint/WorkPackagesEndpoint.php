<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Form\Form;
use OpenEuropa\OpenProjectClient\Model\PageResults;
use OpenEuropa\OpenProjectClient\Model\WorkPackage;
use OpenEuropa\OpenProjectClient\Model\WorkPackageInput;
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
     * @param FilterSet|null $filters
     *   The filters to apply. Null sends an empty filter set, which returns all
     *   work packages rather than only the open ones.
     * @param CollectionParams|null $params
     *   The paging, sorting, and grouping parameters. Iteration starts at the
     *   offset when one is set, and at the first page otherwise.
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
     * @param FilterSet|null $filters
     *   The filters to apply. Null sends an empty filter set, which returns all
     *   work packages rather than only the open ones.
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
     * @param FilterSet|null $filters
     *   The filters to apply. Null sends an empty filter set, which returns all
     *   work packages rather than only the open ones.
     * @param CollectionParams|null $params
     *   The paging, sorting, and grouping parameters. Iteration starts at the
     *   offset when one is set, and at the first page otherwise.
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
     * @param WorkPackageInput|null $payload
     *   The work-package attributes to pre-populate and validate.
     *   Pass null (or omit) to request a blank form.
     * @param int|null $projectId
     *   When provided, the form is scoped to this project.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function createForm(?WorkPackageInput $payload = null, ?int $projectId = null): Form
    {
        $path = $projectId !== null
            ? 'projects/' . $projectId . '/work_packages/form'
            : 'work_packages/form';

        $body = $payload?->toArray() ?? [];

        return Form::fromApiResponse($this->transport->post($path, $body));
    }

    /**
     * Returns the work-package edit form for an existing work package.
     *
     * @param int $id
     *   The ID of the work package to validate the payload against.
     * @param WorkPackageInput $payload
     *   The work-package attributes to validate.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function updateForm(int $id, WorkPackageInput $payload): Form
    {
        return Form::fromApiResponse($this->transport->post('work_packages/' . $id . '/form', $payload->toArray()));
    }

    /**
     * Creates a work package and returns the created resource.
     *
     * @param WorkPackageInput $payload
     *   The work-package attributes to create, including required links.
     * @param bool $notify
     *   Whether to send notifications to involved users.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function create(WorkPackageInput $payload, bool $notify = true): WorkPackage
    {
        return WorkPackage::fromApiResponse(
            $this->transport->post('work_packages', $payload->toArray(), ['notify' => $notify ? 'true' : 'false']),
        );
    }

    /**
     * Updates a work package and returns the updated resource.
     *
     * @param int $lockVersion
     *   The current lock version of the work package, echoed back for optimistic locking.
     * @param bool $notify
     *   Whether to send notifications to involved users.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function update(int $id, WorkPackageInput $payload, int $lockVersion, bool $notify = true): WorkPackage
    {
        return WorkPackage::fromApiResponse(
            $this->transport->patch(
                'work_packages/' . $id,
                ['lockVersion' => $lockVersion] + $payload->toArray(),
                ['notify' => $notify ? 'true' : 'false'],
            ),
        );
    }

    /**
     * Deletes a work package by ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function delete(int $id): void
    {
        $this->transport->delete('work_packages/' . $id);
    }
}
