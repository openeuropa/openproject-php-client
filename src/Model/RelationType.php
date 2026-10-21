<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Types of relation between two work packages.
 */
enum RelationType: string
{
    case RELATES = 'relates';
    case DUPLICATES = 'duplicates';
    case DUPLICATED = 'duplicated';
    case BLOCKS = 'blocks';
    case BLOCKED = 'blocked';
    case PRECEDES = 'precedes';
    case FOLLOWS = 'follows';
    case INCLUDES = 'includes';
    case PART_OF = 'partof';
    case REQUIRES = 'requires';
    case REQUIRED = 'required';
}
