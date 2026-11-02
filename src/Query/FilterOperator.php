<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * Filter operators supported by the OpenProject API collection endpoints.
 */
enum FilterOperator: string
{
    case EQUAL = '=';

    /**
     * Matches a multi-value field holding all the given values.
     */
    case EQUAL_ALL = '&=';

    /**
     * Matches a hierarchy custom field value or any of its descendants.
     */
    case EQUAL_WITH_DESCENDANTS = 'eq_with_descendants';
    case NOT_EQUAL = '!';
    case GREATER_OR_EQUAL = '>=';
    case LESS_OR_EQUAL = '<=';
    case CONTAINS = '~';
    case NOT_CONTAINS = '!~';
    case NOT_NULL = '*';
    case IS_NULL = '!*';
    case SEARCH = '**';
    case ON_DATE = '=d';
    case BETWEEN_DATES = '<>d';
    case TODAY = 't';
    case THIS_WEEK = 'w';
    case N_DAYS_AGO = 't-';
    case LESS_THAN_N_DAYS_AGO = '>t-';
    case MORE_THAN_N_DAYS_AGO = '<t-';
    case N_DAYS_FROM_NOW = 't+';
    case LESS_THAN_N_DAYS_FROM_NOW = '<t+';
    case MORE_THAN_N_DAYS_FROM_NOW = '>t+';
    case OPEN = 'o';
    case CLOSED = 'c';

    /**
     * Matches a locked version, on the work package version filter.
     */
    case LOCKED = 'l';

    /**
     * Matches work packages that have a manual sort order.
     */
    case MANUALLY_SORTED = 'ow';

    // Relation types, for the work package relatable filter.
    case RELATES = 'relates';
    case PRECEDES = 'precedes';
    case FOLLOWS = 'follows';
    case BLOCKS = 'blocks';
    case BLOCKED = 'blocked';
    case DUPLICATES = 'duplicates';
    case DUPLICATED = 'duplicated';
    case INCLUDES = 'includes';
    case PART_OF = 'partof';
    case REQUIRES = 'requires';
    case REQUIRED = 'required';
    case PARENT = 'parent';
    case CHILD = 'child';
    case CHILDREN = 'children';
}
