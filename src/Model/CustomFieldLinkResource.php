<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Resources a reference-type custom field links to.
 */
enum CustomFieldLinkResource: string
{
    /**
     * An option of a list field.
     */
    case CUSTOM_OPTIONS = 'custom_options';
    case USERS = 'users';
    case GROUPS = 'groups';
    case PLACEHOLDER_USERS = 'placeholder_users';
    case VERSIONS = 'versions';

    /**
     * An item of a hierarchy or weighted item list field.
     */
    case CUSTOM_FIELD_ITEMS = 'custom_field_items';
}
