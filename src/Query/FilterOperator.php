<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Query;

/**
 * Filter operators supported by the OpenProject API collection endpoints.
 */
enum FilterOperator: string
{
    case EQUAL = '=';
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
    case N_DAYS_FROM_NOW = 't+';
    case OPEN = 'o';
    case CLOSED = 'c';
}
