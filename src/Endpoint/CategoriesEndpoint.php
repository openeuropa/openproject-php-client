<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Category;
use OpenEuropa\OpenProjectClient\Model\PageResults;

/**
 * Endpoint for reading categories via the OpenProject API.
 */
final class CategoriesEndpoint extends AbstractEndpoint
{
    /**
     * Returns a single category by its ID.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function get(int $id): Category
    {
        return Category::fromApiResponse($this->transport->get('categories/' . $id));
    }

    /**
     * Returns all categories in a project as a single decoded collection.
     *
     * @return PageResults<Category>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function allByProject(int $projectId): PageResults
    {
        return $this->getCollection('projects/' . $projectId . '/categories', Category::fromApiResponse(...));
    }
}
