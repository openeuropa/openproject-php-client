<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Statuses of a version.
 */
enum VersionStatus: string
{
    case OPEN = 'open';
    case LOCKED = 'locked';
    case CLOSED = 'closed';
}
