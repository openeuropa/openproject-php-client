<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Endpoint;

use OpenEuropa\OpenProjectClient\Model\Activity;
use OpenEuropa\OpenProjectClient\Model\PageResults;

/**
 * Endpoint for reading and commenting on work package activities via the OpenProject API.
 */
final class ActivitiesEndpoint extends AbstractEndpoint
{
    /**
     * Returns all activities of a work package as a single decoded collection.
     *
     * @return PageResults<Activity>
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function allByWorkPackage(int $workPackageId): PageResults
    {
        return $this->getCollection(
            'work_packages/' . $workPackageId . '/activities',
            Activity::fromApiResponse(...),
        );
    }

    /**
     * Adds a comment to a work package and returns the created activity.
     *
     * @param bool $notify
     *   Whether to send notifications to involved users.
     *
     * @throws \OpenEuropa\OpenProjectClient\Exception\OpenProjectException
     */
    public function comment(int $workPackageId, string $raw, bool $notify = true): Activity
    {
        return Activity::fromApiResponse(
            $this->transport->post(
                'work_packages/' . $workPackageId . '/activities',
                ['comment' => ['raw' => $raw]],
                ['notify' => $notify ? 'true' : 'false'],
            ),
        );
    }
}
