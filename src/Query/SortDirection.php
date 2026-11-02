<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * Directions a collection is sorted in.
 */
enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
