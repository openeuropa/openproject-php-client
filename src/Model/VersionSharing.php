<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Ways a version is shared between projects.
 */
enum VersionSharing: string
{
    case NONE = 'none';
    case DESCENDANTS = 'descendants';
    case HIERARCHY = 'hierarchy';
    case TREE = 'tree';
    case SYSTEM = 'system';
}
