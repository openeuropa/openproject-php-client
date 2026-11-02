<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Statuses of a project.
 */
enum ProjectStatus: string
{
    case ON_TRACK = 'on_track';
    case AT_RISK = 'at_risk';
    case OFF_TRACK = 'off_track';
    case NOT_STARTED = 'not_started';
    case FINISHED = 'finished';
    case DISCONTINUED = 'discontinued';
}
